<?php

namespace App\Exports;

use App\Services\WooService;
use App\Store;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;

class StoresReportExport implements FromCollection, ShouldAutoSize, WithStrictNullComparison, WithEvents
{
    use Exportable, RegistersEventListeners;

    public function __construct(WooService $woo)
    {
        $this->woo = $woo;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if (!isset($this->year) || !isset($this->month)) {
            return [];
        }

        // Generate report for specified year/month
        $report = $this->generateReport($this->year, $this->month);

        if ($report->isNotempty()) {

            // Insert columns headings
            $headings = array_keys($report[0]);
            $report->prepend($headings);

            // Insert title
            $title = "Stores Report {$this->year}/{$this->month}";
            $report->prepend([$title]);
        }

        return $report;
    }

    public function forMonth($year, $month)
    {
        // Set year/month options
        $this->year = $year;
        $this->month = $month;

        return $this;
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Apply some styling
        $event->sheet->getStyle('A1')->getFont()->setSize(20);
        $event->sheet->getStyle('A1')->getFont()->setBold(true);
        $event->sheet->getStyle('A2:Z2')->getFont()->setBold(true);
    }

    private function generateReport($year, $month)
    {
        // Get stores aggregate status changes data
        $storesData = Store::query()
            ->where('is_superstore', false) // Not dealing with superstores for now
            ->with([
                'statusChanges' => function ($q) use ($year, $month) {
                    $q->whereYear('created_at', '=', $year)
                        ->whereMonth('created_at', '=', $month)
                        ->where('status', 'completed')
                        ->selectRaw('store_id, group_concat(`order`) as orders_numbers')
                        ->groupBy('store_id');
                },
            ])
            ->get();

        $storesData = $storesData
            ->filter(function ($s) {
                // Get rid of inactive stores
                return $s->statusChanges->isNotEmpty();
            })
            ->map(function ($s) {
                // Pre-process the store data
                $statusChanges = $s->statusChanges->first();

                $ordersNumbers = collect(explode(',', $statusChanges['orders_numbers']));
                $orders = $ordersNumbers
                    ->filter(function ($o) {
                        // Remove invalid orders
                        return !empty($o);
                    })
                    ->unique(function ($o) {
                        // Remove duplicates (they happen sometimes)
                        return $o;
                    })->values();

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'orders' => $orders,
                ];
            })->values();

        $shippingData = $storesData
            ->mapWithKeys(function ($storeOrders) {

                $store = Store::find($storeOrders['id']);
                $ordersIds = $storeOrders['orders'];

                // Break the order collection in chunks to deal with WooCommerce pagination issues
                $chunks = $ordersIds->chunk(99);

                // Process each chunk and then merge the result
                return [
                    $storeOrders['id'] =>
                    $chunks->mapWithKeys(function ($chunk) use ($store) {
                        // Fetch WooCommerce data for the required orders
                        $orders = $this->woo->ordersWithId($store, $chunk->toArray());

                        // $shippingData[storeId][orderNumber] contains the order shipping price
                        return $orders->mapWithKeys(function ($o) {return [$o['number'] => $o['shipping']];});
                    }),
                ];
            });

        $storesData = $storesData->map(function ($s) use ($shippingData) {

            // Get list of shipping prices
            $shippingPrices = $s['orders']
                ->filter(function ($o) use ($s, $shippingData) {
                    return isset($shippingData[$s['id']][$o]);
                })
                ->map(function ($o) use ($s, $shippingData) {
                    return $shippingData[$s['id']][$o];
                })
                ->values();

            // Get list of frequencies for each shipping price tier
            $shippingFrequencies = $shippingPrices
                ->groupBy(function ($s) {return $s;})
                ->map(function ($g) {return $g->count();});

            $s['shipping_total'] = $shippingPrices->sum();
            $s['shipping_frequencies'] = $shippingFrequencies;
            $s['deliveries'] = $shippingPrices->count();

            return $s;
        });

        // Get list of all the shipping prices tier
        $shippingTiers = $storesData->map(function ($s) {
            return collect($s['shipping_frequencies'])->keys();
        }, [])->flatten()->unique()->values()->sort();

        $storesData = $storesData->map(function ($s) use ($shippingTiers) {

            foreach ($shippingTiers as $tier) {
                $s["deliveries_{$tier}"] = $s['shipping_frequencies'][$tier] ?? 0;
            }

            // We don't need these anymore
            unset($s['id']);
            unset($s['orders']);
            unset($s['shipping_frequencies']);

            return $s;
        });

        return $storesData;
    }
}
