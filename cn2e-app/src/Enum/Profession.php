<?php

namespace App\Enum;

enum Profession: string
{
    case DIRECTOR = 'Directeur / Directrice';
    case TEACHER = 'Enseignant(e)';
    case EDUCATIONAL_STAFF = 'Personnel éducatif';
    case ADMINISTRATIVE_STAFF = 'Personnel administratif';
    case INSTITUTIONAL_PARTNER = 'Partenaire institutionnel';
    case OTHER = 'Autre';
}