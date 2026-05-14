<?php
// backend/core/CloudinaryUploader.php

class CloudinaryUploader
{
    /**
     * Faz upload de um PDF para o Cloudinary
     * @param array $file — elemento de $_FILES
     * @return array ['url' => string, 'public_id' => string]
     * @throws Exception
     */
    public static function uploadPDF(array $file): array
    {
        // 1. Validações
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do arquivo.');
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Currículo deve ter no máximo 5MB.');
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            throw new Exception('Apenas arquivos PDF são aceitos.');
        }

        // 2. Credenciais
        $cloudName = getEnv2('CLOUDINARY_CLOUD_NAME');
        $apiKey    = getEnv2('CLOUDINARY_API_KEY');
        $apiSecret = getEnv2('CLOUDINARY_API_SECRET');

        // 3. Parâmetros e assinatura
        $timestamp = time();
        $folder    = 'contrataporto/curriculos';
        $params    = [
            'folder'    => $folder,
            'timestamp' => $timestamp,
        ];
        ksort($params);
        $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $signature = hash_hmac('sha256', $paramStr . $apiSecret, $apiSecret);

        // 4. Upload via cURL multipart
        $url  = "https://api.cloudinary.com/v1_1/{$cloudName}/raw/upload";
        $post = [
            'file'      => new CURLFile($file['tmp_name'], 'application/pdf', $file['name']),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Falha ao enviar currículo para o storage. HTTP ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (empty($data['secure_url'])) {
            throw new Exception('Resposta inválida do storage de arquivos.');
        }

        return [
            'url'       => $data['secure_url'],
            'public_id' => $data['public_id'],
        ];
    }
}
