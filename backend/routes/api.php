<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/CompanyController.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../controllers/ApplicationController.php';
require_once __DIR__ . '/../controllers/FavoriteController.php';

$authController = new AuthController();
$userController = new UserController();
$companyController = new CompanyController();
$jobController = new JobController();
$applicationController = new ApplicationController();
$favoriteController = new FavoriteController();

$router->add('POST', '/api/auth/register', fn() => $authController->register());
$router->add('POST', '/api/auth/login', fn() => $authController->login());
$router->add('POST', '/api/auth/recover', fn() => $authController->forgotPassword());
$router->add('POST', '/api/auth/reset', fn() => $authController->resetPassword());
$router->add('POST', '/api/auth/logout', fn() => $authController->logout());
$router->add('POST', '/api/auth/verify-email', fn() => $authController->verifyEmail());
$router->add('POST', '/api/auth/resend-verification', fn() => $authController->resendVerification());

$router->add('POST', '/api/empresas', fn() => $companyController->create());
$router->add('GET', '/api/empresas/{id}', fn(array $params) => $companyController->show($params));

$router->add('GET', '/api/jobs', fn() => $jobController->list());
$router->add('GET', '/api/jobs/filter', fn() => $jobController->filter());
$router->add('GET', '/api/jobs/my-company', fn() => $jobController->myCompanyJobs());
$router->add('GET', '/api/jobs/{id}', fn(array $params) => $jobController->show($params));
$router->add('POST', '/api/jobs', fn() => $jobController->create());
$router->add('PUT', '/api/jobs/{id}', fn(array $params) => $jobController->update($params));
$router->add('DELETE', '/api/jobs/{id}', fn(array $params) => $jobController->delete($params));
$router->add('PUT', '/api/jobs/{id}/conclude', fn(array $params) => $jobController->conclude($params));
$router->add('PUT', '/api/jobs/{id}/status',   fn(array $params) => $jobController->toggleStatus($params));

$router->add('POST', '/api/applications', fn() => $applicationController->apply());
$router->add('GET', '/api/applications/me', fn() => $applicationController->myApplications());
$router->add('GET', '/api/applications/{id}', fn(array $params) => $applicationController->show($params));
$router->add('GET', '/api/applications/{id}/curriculo', fn(array $params) => $applicationController->downloadCurriculo($params));
$router->add('GET', '/api/jobs/{id}/applications', fn(array $params) => $applicationController->jobApplications($params));
$router->add('PUT', '/api/applications/{id}', fn(array $params) => $applicationController->updateStatus($params));

$router->add('POST', '/api/favorites', fn() => $favoriteController->create());
$router->add('GET', '/api/favorites', fn() => $favoriteController->index());
// Perfil do Candidato
$router->add('GET',    '/api/me',      [new UserController(), 'me'],            'ANY');
$router->add('PUT',    '/api/profile', [new UserController(), 'update'],        'CANDIDATO');
$router->add('DELETE', '/api/profile', [new UserController(), 'deleteAccount'], 'CANDIDATO');

// Perfil da Empresa
$router->add('GET',    '/api/empresa/profile',   [new CompanyController(), 'getProfile'],   'EMPRESA');
$router->add('PUT',    '/api/empresa/profile',   [new CompanyController(), 'updateProfile'], 'EMPRESA');
$router->add('DELETE', '/api/empresa/profile',   [new CompanyController(), 'deleteAccount'], 'EMPRESA');

$router->add('DELETE', '/api/favorites/{id}', fn(array $params) => $favoriteController->destroy($params));
