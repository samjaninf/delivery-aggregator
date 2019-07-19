<?php

namespace App\Exports;

use App\Services\WooService;
use App\Store;
use App\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;

class CourierReportExport implements FromCollection, ShouldAutoSize, WithStrictNullComparison, WithEvents
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
            $title = "Report {$this->year}/{$this->month}";
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
        // Get courier aggregate status changes data
        $courierData = User::whereIs('courier')
            ->with([
                'statusChanges' => function ($q) use ($year, $month) {
                    $q->whereYear('created_at', '=', $year)
                        ->whereMonth('created_at', '=', $month)
                        ->where('status', 'completed')
                        ->selectRaw('user_id, group_concat(`order`) as orders_numbers, group_concat(store_id) as stores_ids')
                        ->groupBy('user_id');
                },
                'availabilities' => function ($q) use ($year, $month) {
                    $q->whereYear('start', '=', $year)
                        ->whereMonth('start', '=', $month)
                        ->selectRaw('user_id, count(user_id) as availabilities_number, SUM(TIMESTAMPDIFF(MINUTE, start, end))/60 as availabilities_hours')
                        ->groupBy('user_id');
                },
            ])
            ->get();

        $courierData = $courierData
            ->filter(function ($c) {
                // Get rid of inactive couriers
                return $c->statusChanges->isNotEmpty() || $c->availabilities->isNotEmpty();
            })
            ->map(function ($c) {
                // Pre-process the courier data
                $availabilities = $c->availabilities->first();
                $statusChanges = $c->statusChanges->first();

                // Zip order and store ids together to fetch the required order data later
                $storesIds = collect(explode(',', $statusChanges['stores_ids']));
                $ordersNumbers = collect(explode(',', $statusChanges['orders_numbers']));
                $orders = $storesIds->zip($ordersNumbers)
                    ->map(function ($o) {
                        return [
                            'store' => $o[0],
                            'number' => $o[1],
                        ];
                    })
                    ->filter(function ($o) {
                        // Remove invalid orders
                        return !empty($o['store']) && !empty($o['number']);
                    })
                    ->unique(function ($o) {
                        // Remove duplicates (they happen sometimes)
                        return $o['store'] . '-' . $o['number'];
                    });

                return [
                    // 'id' => $c->id,
                    'name' => $c->name,
                    'availabilities_number' => (float) $availabilities['availabilities_number'],
                    'availabilities_hours' => (float) $availabilities['availabilities_hours'],
                    'orders' => $orders,
                ];
            })->values();

        $ordersToFetch = $courierData->reduce(function ($acc, $c) {
            // Aggregate all necessary orders
            return $acc->concat($c['orders']);
        }, collect([]))->groupBy('store'); // Group the orders by store to fetch them at the same time

        $shippingData = $ordersToFetch
            ->map(function ($storeOrders, $storeIndex) {

                $store = Store::find($storeIndex);
                $ordersIds = $storeOrders->pluck('number');

                // Break the order collection in chunks to deal with WooCommerce pagination issues
                $chunks = $ordersIds->chunk(99);

                // Process each chunk and then merge the result
                return $chunks->mapWithKeys(function ($chunk) use ($store) {
                    // Fetch WooCommerce data for the required orders
                    $orders = $this->woo->ordersWithId($store, $chunk->toArray());

                    // $shippingData[storeId][orderNumber] contains the order shipping price
                    return $orders->mapWithKeys(function ($o) {return [$o['number'] => $o['shipping']];});
                });
            });

        $courierData = $courierData->map(function ($c) use ($shippingData) {

            // Get list of shipping prices
            $shippingPrices = $c['orders']
                ->filter(function ($o) use ($shippingData) {
                    return isset($shippingData[$o['store']][$o['number']]);
                })
                ->map(function ($o) use ($shippingData) {
                    return $shippingData[$o['store']][$o['number']];
                })
                ->values();

            // Get list of frequencies for each shipping price tier
            $shippingFrequencies = $shippingPrices
                ->groupBy(function ($s) {return $s;})
                ->map(function ($g) {return $g->count();});

            $c['shipping_total'] = $shippingPrices->sum();
            $c['shipping_frequencies'] = $shippingFrequencies;
            $c['deliveries'] = $shippingPrices->count();

            return $c;
        });

        // Get list of all the shipping prices tier
        $shippingTiers = $courierData->map(function ($c) {
            return collect($c['shipping_frequencies'])->keys();
        }, [])->flatten()->unique()->values()->sort();

        $courierData = $courierData->map(function ($c) use ($shippingTiers) {

            foreach ($shippingTiers as $tier) {
                $c["deliveries_{$tier}"] = $c['shipping_frequencies'][$tier] ?? 0;
            }

            // We don't need these anymore
            unset($c['orders']);
            unset($c['shipping_frequencies']);

            return $c;
        });

        return $courierData;
    }
}
