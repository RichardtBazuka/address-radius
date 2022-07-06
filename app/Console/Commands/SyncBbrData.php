<?php

namespace App\Console\Commands;

use DOMXPath;
use DOMDocument;
use Carbon\Carbon;
use App\Enums\RuleType;
use App\Models\Field;
use App\Models\Value;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncBbrData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'al:sync-bbr-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $systemNames = [
        'BygAnvendelse'          => 'BYG_ANVEND_KODE',
        /*

        */
        'YdervaeggenesMateriale' => 'YDERVAEG_KODE',
        'Tagdaekningsmateriale'  => 'TAG_KODE',
        'BygAfloebsforhold'      => 'BYG_AFLOEB_KODE',
        
        /* TODO */

        'Varmeinstallation'             => 'VARMEINSTAL_KODE',
        'Supplerende varmeinstallation' => 'VARME_SUPPL_KODE',
        'Opvarmningsmiddel'             => 'OPVARMNING_KODE',
        'Boligtype'                     => 'BOLIGTYPE_KODE',
        'Udlejningsforhold'             => 'ENH_UDLEJ1_KODE',
    ];

    protected $ranges = [
        'Opført år' => 'OPFOERELSE_AAR',
        'Ombygget'  => 'OMBYG_AAR',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = 'http://teknik.bbr.dk/kodelister';
        $response = Http::get($url);
        
        $document = new DOMDocument();
        $document->loadHTML($response->body(), LIBXML_NOERROR); 
        $xpath = new DOMXPath($document);
        $keys = $xpath->evaluate('/html/body/main/div/section/div/div/div/div/select/option');

        $data = [];
        foreach ($keys as $key) {
            foreach ($key->attributes as $attribute) {
                if ($attribute->nodeName != 'value') {
                    continue;
                }
                
                if ($attribute->value == $url) {
                    continue;
                }

                $value = explode('/', $attribute->value);
                $value = array_pop($value);

                $field = Field::firstOrCreate([
                    'name'        => $value,
                    'system_name' => $this->systemNames[$value] ?? $value,
                ]);

                $this->getFieldValues($field, $attribute->value);
                $data[] = $value;
            }
        }

        return Command::SUCCESS;
    }

    private function getFieldValues($field, $url)
    {
        $response = Http::get($url);
        
        $document = new DOMDocument();
        $document->loadHTML($response->body(), LIBXML_NOERROR); 
        $xpath = new DOMXPath($document);
        $keys = $xpath->evaluate('/html/body/main/div/div[3]/div/div/ul/li');

        foreach ($keys as $key) {
            foreach ($key->childNodes as $node) {
                $value = explode(' - ', $node->data);

                Value::updateOrCreate(
                    [
                        'field_id' => $field->id,
                        'value'    => $value[1]
                    ],
                    [
                        'enum'  => $value[0]
                    ]
                );

                $this->info('Attached ' . $node->data . ' to ' . $field->name);
            }
        }

        return;
    }
}
