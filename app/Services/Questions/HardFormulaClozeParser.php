<?php

declare(strict_types=1);

namespace App\Services\Questions;

class HardFormulaClozeParser
{
    
    
   /**
 * @return list<array{value: string, tolerance: float|null}>
 */
   public function extractExpectedValues(string $content): array
   {
       // Limpiar zero-width spaces y otros caracteres invisibles
       $content = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $content);
   
       if (! preg_match_all('/\[(.*?)\]/', $content, $matches)) {
           return [];
       }
   
       return array_map(function (string $raw) {
           // Limpiar espacios normales e invisibles
           $raw = trim($raw);
           $raw = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $raw);
           $raw = trim($raw);
   
           // Soporta 13.33±0.05  o  13,33±0.05  o  13.33:0.05
           if (preg_match('/^(.+?)(?:±|:)(.+)$/u', $raw, $m)) {
               return [
                   'value'     => trim($m[1]),
                   'tolerance' => (float) str_replace(',', '.', trim($m[2])),
               ];
           }
   
           return [
               'value'     => $raw,
               'tolerance' => null,
           ];
       }, $matches[1]);
   }

    public function holeCount(string $content): int
    {
        return count($this->extractExpectedValues($content));
    }

    public function buildStudentLatex(string $content, int $optionId): string
{
    $index = 0;

    return (string) preg_replace_callback(
        '/\[(.*?)\]/',
        function () use ($optionId, &$index) {
            $marker = 'ZHOLEZ'.$optionId.'Z'.$index.'Z';
            $index++;

            return '\\text{'.$marker.'}';
        },
        $content
    );
}
}
