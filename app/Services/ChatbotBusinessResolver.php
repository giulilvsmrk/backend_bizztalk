<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Negocio;
use Illuminate\Support\Collection;

class ChatbotBusinessResolver
{
    private const SIMILARITY_THRESHOLD = 80;

    /**
     * Resolver negocio por texto del usuario usando fuzzy matching
     * 
     * @param string $userText Texto escrito por el usuario
     * @return array
     */
    public function resolve(string $userText): array
    {
        $userText = $this->normalize($userText);
        $negocios = Negocio::where('activo', true)->get();

        if ($negocios->isEmpty()) {
            return $this->notFound();
        }

        $matches = $this->findMatches($userText, $negocios);

        if (empty($matches)) {
            return $this->notFound();
        }

        if (count($matches) > 1) {
            return $this->ambiguous($matches);
        }

        $negocio = $matches[0]['negocio'];
        return $this->success($negocio);
    }

    /**
     * Normalizar texto: minúsculas, sin acentos
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[áàä]/u', 'a', $text);
        $text = preg_replace('/[éèë]/u', 'e', $text);
        $text = preg_replace('/[íìï]/u', 'i', $text);
        $text = preg_replace('/[óòö]/u', 'o', $text);
        $text = preg_replace('/[úùü]/u', 'u', $text);
        return preg_replace('/[^a-z0-9\s]/u', '', $text);
    }

    /**
     * Encontrar negocios con similitud >= threshold
     */
    private function findMatches(string $userText, Collection $negocios): array
    {
        $matches = [];

        foreach ($negocios as $negocio) {
            $negocioName = $this->normalize($negocio->nombre);
            
            // Comparar el texto completo
            similar_text($userText, $negocioName, $percentFull);
            
            // Comparar también palabra por palabra (más permisivo)
            $percentPartial = $this->compareByWords($userText, $negocioName);
            
            // Usar el porcentaje más alto de ambas comparaciones
            $percent = max($percentFull, $percentPartial);

            if ($percent >= self::SIMILARITY_THRESHOLD) {
                $matches[] = [
                    'negocio' => $negocio,
                    'similarity' => $percent
                ];
            }
        }

        // Ordenar por similitud descendente
        usort($matches, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $matches;
    }

    /**
     * Comparar por palabras individuales para mejor matching
     */
    private function compareByWords(string $userText, string $negocioName): float
    {
        $userWords = array_filter(explode(' ', $userText));
        $negocioWords = array_filter(explode(' ', $negocioName));

        if (empty($negocioWords)) {
            return 0;
        }

        $matches = 0;
        foreach ($userWords as $userWord) {
            foreach ($negocioWords as $negocioWord) {
                similar_text($userWord, $negocioWord, $percent);
                if ($percent >= 70) {
                    $matches++;
                    break;
                }
            }
        }

        return ($matches / count($negocioWords)) * 100;
    }

    /**
     * Respuesta cuando se encuentra el negocio
     */
    private function success(Negocio $negocio): array
    {
        // Obtener productos activos del negocio
        $productos = $negocio->productos()
            ->where('activo', true)
            ->select(['id', 'nombre', 'descripcion', 'precio_base'])
            ->limit(50)
            ->get();

        return $productos->map(fn($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'descripcion' => $p->descripcion,
            'precio' => (float)$p->precio_base
        ])->values()->all();
    }

    /**
     * Respuesta cuando hay múltiples coincidencias
     */
    private function ambiguous(array $matches): array
    {
        $names = array_map(
            fn($match) => $match['negocio']->nombre,
            array_slice($matches, 0, 3)
        );

        return [
            'status' => 'ambiguous',
            'message' => '¿Te refieres a ' . implode(' o ', $names) . '?',
            'suggestions' => $names
        ];
    }

    /**
     * Respuesta cuando no se encuentra coincidencia
     */
    private function notFound(): array
    {
        return [
            'status' => 'not_found',
            'message' => 'No se encontró ningún negocio similar a lo que escribiste.'
        ];
    }
}
