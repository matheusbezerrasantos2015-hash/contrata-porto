<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\EmailVerification;
use App\Models\Favorite;
use App\Models\JobListing;
use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContrataPortoApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration and verification flows.
     */
    public function test_auth_registration_and_verification_flow(): void
    {
        // 1. Register candidate
        $response = $this->postJson('/api/auth/register', [
            'nome'  => 'Candidato Teste',
            'email' => 'candidato@test.com',
            'senha' => 'password123',
            'role'  => 'CANDIDATO',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('data.requires_verification', true);

        $this->assertDatabaseHas('users', [
            'email' => 'candidato@test.com',
            'tipo'  => 'CANDIDATO',
        ]);

        // Verification token/code should be generated
        $verification = EmailVerification::whereHas('user', function ($q) {
            $q->where('email', 'candidato@test.com');
        })->first();
        $this->assertNotNull($verification);
        $code = $verification->code;

        // 2. Try to login before verifying email (should fail with 403)
        $loginFail = $this->postJson('/api/auth/login', [
            'email' => 'candidato@test.com',
            'senha' => 'password123',
        ]);
        $loginFail->assertStatus(403)
                  ->assertJsonPath('success', false)
                  ->assertJsonPath('data.requires_verification', true);

        // 3. Verify email
        $verifyResponse = $this->postJson('/api/auth/verify-email', [
            'email' => 'candidato@test.com',
            'code'  => $code,
        ]);
        $verifyResponse->assertStatus(200)
                       ->assertJsonPath('success', true);

        // User should now be verified
        $user = User::where('email', 'candidato@test.com')->first();
        $this->assertNotNull($user->email_verified_at);

        // 4. Login after verification (should succeed and return token)
        $loginSuccess = $this->postJson('/api/auth/login', [
            'email' => 'candidato@test.com',
            'senha' => 'password123',
        ]);
        $loginSuccess->assertStatus(200)
                     ->assertJsonPath('success', true)
                     ->assertJsonStructure(['data' => ['usuario', 'auth' => ['token']]]);
    }

    /**
     * Test candidate profile endpoints.
     */
    public function test_candidate_profile_endpoints(): void
    {
        $user = User::factory()->create([
            'tipo'              => 'CANDIDATO',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // GET /api/me
        $responseMe = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');
        
        $responseMe->assertStatus(200)
                   ->assertJsonPath('success', true)
                   ->assertJsonPath('data.nome', $user->nome);

        // PUT /api/profile
        $responseUpdate = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/profile', [
                'nome'     => 'Novo Nome Candidato',
                'telefone' => '19999999999',
                'cidade'   => 'Porto Ferreira',
                'estado'   => 'SP',
            ]);

        $responseUpdate->assertStatus(200)
                       ->assertJsonPath('success', true)
                       ->assertJsonPath('data.nome', 'Novo Nome Candidato');

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'nome' => 'Novo Nome Candidato',
        ]);

        // DELETE /api/profile
        $responseDelete = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/profile');

        $responseDelete->assertStatus(200)
                       ->assertJsonPath('success', true);

        // Soft deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test company registration, profile management, and job creation.
     */
    public function test_company_profile_and_job_management(): void
    {
        // 1. Create company user
        $user = User::factory()->create([
            'tipo'              => 'EMPRESA',
            'email_verified_at' => now(),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create company details
        $companyResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/empresas', [
                'nome_fantasia' => 'Empresa Teste',
                'cnpj'          => '12.345.678/0001-90',
                'descricao'     => 'Empresa de testes unitários',
                'site'          => 'https://empresateste.com',
            ]);

        $companyResponse->assertStatus(201)
                        ->assertJsonPath('success', true);

        $company = Company::where('user_id', $user->id)->first();
        $this->assertNotNull($company);
        $this->assertEquals('12345678000190', $company->cnpj); // sanitized

        // 2. GET /api/empresa/profile
        $this->app['auth']->forgetUser();
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/empresa/profile');
        $profileResponse->assertStatus(200)
                        ->assertJsonPath('success', true)
                        ->assertJsonPath('data.nome_fantasia', 'Empresa Teste');

        // 3. PUT /api/empresa/profile
        $updateProfile = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/empresa/profile', [
                'nome_fantasia' => 'Empresa Teste Atualizada',
                'telefone'      => '1988888888',
                'descricao'     => 'Nova descrição',
                'site'          => 'https://novaempresateste.com',
            ]);
        $updateProfile->assertStatus(200)
                      ->assertJsonPath('success', true);

        $token = $updateProfile->json('data.token');
        $this->app['auth']->forgetUser();

        $this->assertDatabaseHas('companies', [
            'id'            => $company->id,
            'nome_fantasia' => 'Empresa Teste Atualizada',
        ]);

        // 4. POST /api/jobs (Create job listing)
        $jobResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/jobs', [
                'titulo'    => 'Desenvolvedor PHP Laravel',
                'cargo'     => 'Programador Laravel',
                'area'      => 'Tecnologia',
                'descricao' => 'Trabalhar com Laravel 11 e SQLite',
                'tipo'      => 'CLT',
            ]);

        $jobResponse->assertStatus(201)
                    ->assertJsonPath('success', true)
                    ->assertJsonPath('data.titulo', 'Desenvolvedor PHP Laravel');

        $job = JobListing::where('company_id', $company->id)->first();
        $this->assertNotNull($job);

        // 5. GET /api/jobs/my-company
        $myJobs = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/jobs/my-company');
        $myJobs->assertStatus(200)
               ->assertJsonPath('success', true)
               ->assertJsonStructure(['data' => ['vagas', 'total']]);

        // 6. PUT /api/jobs/{id}/status (Toggle active status)
        $toggleResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/jobs/{$job->id}/status");
        $toggleResponse->assertStatus(200)
                       ->assertJsonPath('success', true)
                       ->assertJsonPath('data.status', 'PAUSADA');

        $this->assertFalse(JobListing::find($job->id)->ativo);

        // Toggle back to active
        $this->withHeader('Authorization', 'Bearer ' . $token)->putJson("/api/jobs/{$job->id}/status");
        $this->assertTrue(JobListing::find($job->id)->ativo);

        // 7. PUT /api/jobs/{id}/conclude
        $concludeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/jobs/{$job->id}/conclude");
        $concludeResponse->assertStatus(200)
                         ->assertJsonPath('success', true);

        $freshJob = JobListing::find($job->id);
        $this->assertNotNull($freshJob->expires_at);
        $this->assertNotNull($freshJob->encerrada_em);
    }

    /**
     * Test job applications and status changes.
     */
    public function test_job_application_flow(): void
    {
        // 1. Setup company, job, and candidate
        $companyUser = User::factory()->create(['tipo' => 'EMPRESA', 'email_verified_at' => now()]);
        $companyToken = $companyUser->createToken('auth_token')->plainTextToken;
        $company = Company::create([
            'user_id'       => $companyUser->id,
            'nome_fantasia' => 'Empresa Vaga',
            'cnpj'          => '11111111000111',
        ]);
        $job = JobListing::create([
            'company_id' => $company->id,
            'titulo'     => 'Vaga de Teste',
            'descricao'  => 'Detalhes da vaga de teste',
            'ativo'      => true,
        ]);

        $candidateUser = User::factory()->create(['tipo' => 'CANDIDATO', 'email_verified_at' => now()]);
        $candidateToken = $candidateUser->createToken('auth_token')->plainTextToken;

        // 2. Candidate applies for the job
        $applyResponse = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->postJson('/api/applications', [
                'job_id'    => $job->id,
                'mensagem'  => 'Gostaria muito desta vaga!',
                'linkedin'  => 'https://linkedin.com/in/teste',
                'portfolio' => 'https://portfolio.com',
                'telefone'  => '19999999999',
            ]);

        $applyResponse->assertStatus(201)
                      ->assertJsonPath('success', true);

        $application = Application::where('user_id', $candidateUser->id)->where('job_id', $job->id)->first();
        $this->assertNotNull($application);
        $this->assertEquals('pendente', $application->status);

        // 3. Candidate lists my-applications
        $this->app['auth']->forgetUser();
        $myApps = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->getJson('/api/applications/me');
        $myApps->assertStatus(200)
               ->assertJsonPath('success', true)
               ->assertJsonStructure(['data' => ['items', 'meta']]);

        // 4. Company lists job applications
        $this->app['auth']->forgetUser();
        $jobApps = $this->withHeader('Authorization', 'Bearer ' . $companyToken)
            ->getJson("/api/jobs/{$job->id}/applications");
        $jobApps->assertStatus(200)
                ->assertJsonPath('success', true)
                ->assertJsonCount(1, 'data.items');

        // 5. Company updates application status
        $this->app['auth']->forgetUser();
        $statusResponse = $this->withHeader('Authorization', 'Bearer ' . $companyToken)
            ->putJson("/api/applications/{$application->id}", [
                'status' => 'em_analise',
            ]);
        $statusResponse->assertStatus(200)
                       ->assertJsonPath('success', true);

        $this->assertEquals('em_analise', $application->fresh()->status);
    }

    /**
     * Test favoriting jobs.
     */
    public function test_favorites_flow(): void
    {
        $companyUser = User::factory()->create(['tipo' => 'EMPRESA', 'email_verified_at' => now()]);
        $company = Company::create([
            'user_id'       => $companyUser->id,
            'nome_fantasia' => 'Empresa Fav',
            'cnpj'          => '22222222000122',
        ]);
        $job = JobListing::create([
            'company_id' => $company->id,
            'titulo'     => 'Vaga Fav',
            'descricao'  => 'Vaga que sera favoritada',
            'ativo'      => true,
        ]);

        $candidateUser = User::factory()->create(['tipo' => 'CANDIDATO', 'email_verified_at' => now()]);
        $candidateToken = $candidateUser->createToken('auth_token')->plainTextToken;

        // 1. Add to favorites
        $favResponse = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->postJson('/api/favorites', [
                'job_id' => $job->id,
            ]);
        $favResponse->assertStatus(201)
                    ->assertJsonPath('success', true);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $candidateUser->id,
            'job_id'  => $job->id,
        ]);

        // 2. List favorites
        $listResponse = $this->withHeader('Authorization', 'Bearer ' . $candidateToken)
            ->getJson('/api/favorites');
        $listResponse->assertStatus(200)
                     ->assertJsonPath('success', true)
                     ->assertJsonCount(1, 'data');
    }

    /**
     * Test relationship directly.
     */
    public function test_user_company_relationship_directly(): void
    {
        $user = User::factory()->create(['tipo' => 'EMPRESA']);
        $company = Company::create([
            'user_id' => $user->id,
            'nome_fantasia' => 'Empresa Teste Relação',
            'cnpj' => '99999999999999',
        ]);

        $this->assertNotNull($user->company);
        $this->assertEquals('Empresa Teste Relação', $user->company->nome_fantasia);
    }
}
