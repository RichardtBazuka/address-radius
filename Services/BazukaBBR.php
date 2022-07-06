<?php
namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BazukaBBR
{
    const BASE_URL = 'https://api.bazuka.dk/bbr/v2/get.php';

    public static function lookUpFromRequest(Request $request)
    {
        $groups = [
            [
                'street_name',
                'number',
            ],
            [
                'zip',
                'city',
            ]
        ];

        $query = [];
        $string = '';

        foreach ($groups as $group) {
            if ($query) {
                $query = [];
                $string .= ', ';
            }

            foreach ($group as $content) {
                $query[] = Arr::get($request, $content);
            }

            $string .= implode(' ', $query);
        }

        return self::lookUp($string);
    }

    public static function lookUp(string $query)
    {
        $response = Http::get(self::BASE_URL, ['address' => $query]);

        return $response->json();
    }

    public static function keyValues()
    {
        $response = Http::get(self::BASE_URL, ['keyvalues' => 1]);
        
        return $response->json();
    }
}