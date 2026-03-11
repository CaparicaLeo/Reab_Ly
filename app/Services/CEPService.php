<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CEPService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.external_api.cep.base_url');
    }

    public function findAddressByCEP(string $cep)
    {
        $cep = preg_replace('/\D/', '', $cep); // remove traço e ponto

        $response = Http::get("{$this->baseUrl}/{$cep}");

        if ($response->failed()) {
            throw new \Exception('CEP não encontrado.');
        }

        return $response->json();
    }
}
