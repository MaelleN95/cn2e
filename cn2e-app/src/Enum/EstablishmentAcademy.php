<?php

namespace App\Enum;

enum EstablishmentAcademy: string
{
    case AIX_MARSEILLE = 'Aix-Marseille';
    case AMIENS = 'Amiens';
    case BESANCON = 'Besançon';
    case BORDEAUX = 'Bordeaux';
    case CLERMONT = 'Clermont';
    case CORSE = 'Corse';
    case CRETEIL = 'Créteil';
    case DIJON = 'Dijon';
    case GRENOBLE = 'Grenoble';
    case GUADELOUPE = 'Guadeloupe';
    case GUYANE = 'Guyane';
    case LA_REUNION = 'La Réunion';
    case LILLE = 'Lille';
    case LIMOGES = 'Limoges';
    case LYON = 'Lyon';
    case MARTINIQUE = 'Martinique';
    case MAYOTTE = 'Mayotte';
    case MONTPELLIER = 'Montpellier';
    case NANCY_METZ = 'Nancy-Metz';
    case NANTES = 'Nantes';
    case NICE = 'Nice';
    case NORMANDIE = 'Normandie';
    case ORLEANS_TOURS = 'Orléans-Tours';
    case PARIS = 'Paris';
    case POITIERS = 'Poitiers';
    case REIMS = 'Reims';
    case RENNES = 'Rennes';
    case STRASBOURG = 'Strasbourg';
    case TOULOUSE = 'Toulouse';
    case VERSAILLES = 'Versailles';

    public static function values(): array
    {
        return array_map(static fn(self $academy) => $academy->value, self::cases());
    }

    public static function choices(): array
    {
        $choices = [];

        foreach (self::cases() as $academy) {
            $choices[$academy->value] = $academy->value;
        }

        return $choices;
    }
}
