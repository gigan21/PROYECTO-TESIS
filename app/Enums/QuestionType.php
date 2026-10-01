<?php

namespace App\Enums;

enum QuestionType: string
{
    case Conceptual = 'Conceptual';
    case Aplicacion = 'Aplicación';
    case Calculo = 'Cálculo';
    case Analisis = 'Análisis';
    case Problema = 'Problema';
}
