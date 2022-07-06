<?php
namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Dawa
{
    const URL = 'https://api.dataforsyningen.dk/adresser';

    const SEQUENCE = [
        'https://api.dataforsyningen.dk/datavask/adresser' => [
            'param' => 'betegnelse',
            'key'   => 'resultater.0.adresse.adgangsadresseid',
        ],
        'https://api.dataforsyningen.dk/bbrlight/bygninger' => [
            'param' => 'adgangsadresseid',
            'key'   => '',
        ]
    ];

    const PRIMARY_BUILDING = 1;

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
        foreach (self::SEQUENCE as $url => $handles) {
            $response = Http::get($url, [$handles['param'] => $query]);
            if ($handles['key']) {
                $query = Arr::get($response->json(), $handles['key']);
            }
        }

        $data = $response->json();

        $element = [];
        if (count($data)) {
            foreach ($data as $building) {
                if ($building['Bygningsnr'] == self::PRIMARY_BUILDING) {
                    $element = $element ?: $building;

                    if ($element['BYG_BOLIG_ARL_SAML'] < $building['BYG_BOLIG_ARL_SAML']) {
                        $element = $building;
                    }
                }
            }
        }

        return $element;
    }

    public static function AddressesNearby($long, $lat, $radius)
    {
        $response = Http::get(self::URL, [
            'cirkel'   => implode(',', [$long, $lat, $radius]),
            'struktur' => 'mini'
        ]);

        return $response->json();
    }
}