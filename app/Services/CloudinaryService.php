<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME');
        $this->apiKey    = config('services.cloudinary.api_key') ?? env('CLOUDINARY_API_KEY');
        $this->apiSecret = config('services.cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET');
    }

    /**
     * Upload de currículo PDF para o Cloudinary.
     * 
     * @param UploadedFile $file
     * @return array ['url' => string, 'public_id' => string]
     * @throws Exception
     */
    public function uploadPDF(UploadedFile $file): array
    {
        if (!$file->isValid()) {
            throw new Exception('Arquivo inválido para upload.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new Exception('O currículo deve ter no máximo 5MB.');
        }

        if ($file->getMimeType() !== 'application/pdf') {
            throw new Exception('Apenas arquivos PDF são aceitos.');
        }

        $timestamp = time();
        $folder    = 'contrataporto/curriculos';

        // Geração da assinatura (SHA-1)
        $paramStr  = "access_mode=public&folder={$folder}&timestamp={$timestamp}&type=upload";
        $signature = sha1($paramStr . $this->apiSecret);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/raw/upload";

        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post($url, [
            'api_key'     => $this->apiKey,
            'timestamp'   => $timestamp,
            'folder'      => $folder,
            'access_mode' => 'public',
            'type'        => 'upload',
            'signature'   => $signature,
        ]);

        if (!$response->successful()) {
            $msg = $response->json()['error']['message'] ?? 'Erro desconhecido (' . $response->status() . ')';
            throw new Exception('Falha ao enviar arquivo para o Cloudinary: ' . $msg);
        }

        $data = $response->json();

        return [
            'url'       => $data['secure_url'],
            'public_id' => $data['public_id'],
        ];
    }

    /**
     * Upload de imagem (avatar / logo) para o Cloudinary.
     * Aceita: image/jpeg, image/png, image/webp — máximo 2 MB.
     *
     * @param UploadedFile $file
     * @param string $folder  Pasta no Cloudinary (ex: 'contrataporto/avatars')
     * @return array ['url' => string, 'public_id' => string]
     * @throws Exception
     */
    public function uploadImageFile(UploadedFile $file, string $folder = 'contrataporto/images'): array
    {
        if (!$file->isValid()) {
            throw new Exception('Arquivo inválido para upload.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new Exception('A imagem deve ter no máximo 2 MB.');
        }

        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new Exception('Apenas imagens JPEG, PNG ou WebP são aceitas.');
        }

        $timestamp = time();

        // Geração da assinatura (SHA-1)
        $paramStr  = "folder={$folder}&timestamp={$timestamp}";
        $signature = sha1($paramStr . $this->apiSecret);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post($url, [
            'api_key'   => $this->apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ]);

        if (!$response->successful()) {
            $msg = $response->json()['error']['message'] ?? 'Erro desconhecido (' . $response->status() . ')';
            throw new Exception('Falha ao enviar imagem para o Cloudinary: ' . $msg);
        }

        $data = $response->json();

        return [
            'url'       => $data['secure_url'],
            'public_id' => $data['public_id'],
        ];
    }

    /**
     * Deleta um arquivo do Cloudinary.
     * 
     * @param string $publicId
     * @param string $resourceType 'raw' ou 'image'
     * @return bool
     */
    public function deleteFile(string $publicId, string $resourceType = 'raw'): bool
    {
        try {
            $timestamp = time();
            $paramStr  = "public_id={$publicId}&timestamp={$timestamp}";
            $signature = sha1($paramStr . $this->apiSecret);

            $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/destroy";

            $response = Http::post($url, [
                'public_id' => $publicId,
                'api_key'   => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return isset($result['result']) && $result['result'] === 'ok';
            }

            Log::error("[CLOUDINARY_DELETE_ERROR] Falha ao deletar arquivo {$publicId}: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("[CLOUDINARY_DELETE_ERROR] Exceção ao deletar arquivo {$publicId}: " . $e->getMessage());
            return false;
        }
    }
}
