<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameProperty;
use App\Models\Property;
use App\Models\Setting;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'settings' => $this->bank->settingsPayload(),
            'properties' => $this->properties(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'starting_balance' => ['required', 'integer', 'min:1'],
            'go_bonus' => ['required', 'integer', 'min:0'],
            'tax_amount' => ['required', 'integer', 'min:0'],
            'fine_amount' => ['required', 'integer', 'min:0'],
            'starting_cash' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data as $key => $value) {
            Setting::putValue($key, (int) $value);
        }

        return response()->json([
            'message' => 'Settings berhasil disimpan.',
            'settings' => $this->bank->settingsPayload(),
        ]);
    }

    public function storeProperty(Request $request): JsonResponse
    {
        $property = Property::query()->create($this->propertyData($request));

        return response()->json([
            'message' => 'Properti baru ditambahkan.',
            'property' => $this->propertyPayload($property),
            'properties' => $this->properties(),
        ], 201);
    }

    public function updateProperty(Request $request, Property $property): JsonResponse
    {
        $property->update($this->propertyData($request, $property->id));

        return response()->json([
            'message' => 'Properti berhasil diperbarui.',
            'property' => $this->propertyPayload($property->fresh()),
            'properties' => $this->properties(),
        ]);
    }

    public function destroyProperty(Property $property): JsonResponse
    {
        if (GameProperty::query()->where('property_id', $property->id)->exists()) {
            throw ValidationException::withMessages([
                'property' => 'Properti sudah dipakai pada game. Edit saja agar history tetap aman.',
            ]);
        }

        $property->delete();

        return response()->json([
            'message' => 'Properti berhasil dihapus.',
            'properties' => $this->properties(),
        ]);
    }

    public function importProperties(Request $request): JsonResponse
    {
        $request->validate([
            'csv' => ['required', 'file'],
        ]);

        $handle = fopen($request->file('csv')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if (! data_get($data, 'name')) {
                continue;
            }

            Property::query()->updateOrCreate(
                ['name' => data_get($data, 'name')],
                [
                    'price' => (int) data_get($data, 'price', 0),
                    'house_price' => (int) data_get($data, 'house_price', 0),
                    'hotel_price' => (int) data_get($data, 'hotel_price', 0),
                    'rent' => (int) data_get($data, 'rent', 0),
                    'rent_1_house' => (int) data_get($data, 'rent_1_house', 0),
                    'rent_2_houses' => (int) data_get($data, 'rent_2_houses', 0),
                    'rent_3_houses' => (int) data_get($data, 'rent_3_houses', 0),
                    'rent_4_houses' => (int) data_get($data, 'rent_4_houses', 0),
                    'rent_hotel' => (int) data_get($data, 'rent_hotel', 0),
                    'color' => data_get($data, 'color', '#10b981'),
                    'sort_order' => (int) data_get($data, 'sort_order', 0),
                ]
            );

            $count++;
        }

        fclose($handle);

        return response()->json([
            'message' => "{$count} properti berhasil diimpor.",
            'properties' => $this->properties(),
        ]);
    }

    public function exportProperties(): StreamedResponse
    {
        $headers = [
            'name',
            'price',
            'house_price',
            'hotel_price',
            'rent',
            'rent_1_house',
            'rent_2_houses',
            'rent_3_houses',
            'rent_4_houses',
            'rent_hotel',
            'color',
            'sort_order',
        ];

        return Response::streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);

            Property::query()->orderBy('sort_order')->each(function (Property $property) use ($out, $headers) {
                fputcsv($out, collect($headers)->map(fn (string $key) => $property->{$key})->all());
            });

            fclose($out);
        }, 'monopoly-properties.csv', ['Content-Type' => 'text/csv']);
    }

    private function propertyData(Request $request, ?int $ignoreId = null): array
    {
        $uniqueName = 'unique:properties,name';
        if ($ignoreId) {
            $uniqueName .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:120', $uniqueName],
            'price' => ['required', 'integer', 'min:0'],
            'house_price' => ['required', 'integer', 'min:0'],
            'hotel_price' => ['required', 'integer', 'min:0'],
            'rent' => ['required', 'integer', 'min:0'],
            'rent_1_house' => ['required', 'integer', 'min:0'],
            'rent_2_houses' => ['required', 'integer', 'min:0'],
            'rent_3_houses' => ['required', 'integer', 'min:0'],
            'rent_4_houses' => ['required', 'integer', 'min:0'],
            'rent_hotel' => ['required', 'integer', 'min:0'],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function properties(): array
    {
        return Property::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Property $property) => $this->propertyPayload($property))
            ->values()
            ->all();
    }

    private function propertyPayload(Property $property): array
    {
        return [
            'id' => $property->id,
            'name' => $property->name,
            'price' => $property->price,
            'house_price' => $property->house_price,
            'hotel_price' => $property->hotel_price,
            'rent' => $property->rent,
            'rent_1_house' => $property->rent_1_house,
            'rent_2_houses' => $property->rent_2_houses,
            'rent_3_houses' => $property->rent_3_houses,
            'rent_4_houses' => $property->rent_4_houses,
            'rent_hotel' => $property->rent_hotel,
            'color' => $property->color,
            'sort_order' => $property->sort_order,
        ];
    }
}
