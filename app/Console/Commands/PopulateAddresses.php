<?php

namespace App\Console\Commands;

use App\Services\Dawa;
use App\Models\Address;
use App\Services\BazukaBBR;
use Illuminate\Support\Arr;
use App\Models\AddressLookup;
use Illuminate\Console\Command;

class PopulateAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'addresses:lookup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $baseHouse = AddressLookup::whereCompleted(false)->get()->random();

        if (!$baseHouse) {
            $this->info('No house is found');
            return 0;
        }

        $addresses = Dawa::addressesNearby(
            Arr::get($baseHouse, 'long'),
            Arr::get($baseHouse, 'lat'),
            Arr::get($baseHouse, 'radius')
        );

        $addresses = $this->getAddresses($addresses);

        $previouslyStored = Address::whereAddressLookupId($baseHouse->id)
            ->get()
            ->pluck('address')
            ->toArray();

        $potential = [];
        foreach ($addresses as $address) {
            if (in_array($address, $previouslyStored)) {
                $this->info('skipped - previously stored');
                continue;
            }

            $house = BazukaBBR::lookUp($address);
            $potential[] = Arr::get($house, 'address.adgangsadresse.address');

/*
            if (Arr::get($house, 'area.total') < 80) {
                continue;
            }

            if (Arr::get($house, 'area.total') > 300) {
                continue;
            }
*/

            if (Arr::get($house, 'general.rent_type') != 'Benyttet af ejeren') {
                $this->info(Arr::get($house, 'general.rent_type'));
                continue;
            }

            $address = Arr::get($house, 'address.adgangsadresse.address');
            if ($address) {
                Address::create([
                    'address' => $address,
                    'area'    => Arr::get($house, 'area.total'),
                    'heating' => Arr::get($house, 'heating.warmup'),
                    'used_by' => Arr::get($house, 'general.rent_type'),
                    'type'    => Arr::get($house, 'general.building_type'),
                    'address_lookup_id' => Arr::get($baseHouse, 'id'),
                ]);
            }
        }

        $baseHouse->completed = true;
        $baseHouse->save();

        $this->info('');
        $this->info('DONE');

        return 0;
    }

    private function getAddresses($addressData)
    {
        $result = [];

        foreach ($addressData as $data) {
            $result[] = Arr::get($data, 'vejnavn') . ' ' .
                Arr::get($data, 'husnr') . ', ' .
                Arr::get($data, 'postnr') . ' ' .
                Arr::get($data, 'postnrnavn');
        }

        return $result;
    }
}
