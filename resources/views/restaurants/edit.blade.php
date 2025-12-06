@extends('layouts.app')

@section('content')
    @php
        $getir = json_decode($restaurant->getir, true) ?? [];
        $ys = json_decode($restaurant->yemeksepeti, true) ?? [];
        $migros = json_decode($restaurant->migros, true) ?? [];
        $trendyol = json_decode($restaurant->trendyol, true) ?? [];
    @endphp

    <div class="container mx-auto p-8">
        <h2 class="text-3xl font-bold mb-8">Restaurant Düzenle</h2>

        <div id="alertBox" class="hidden mb-4 p-3 rounded text-white"></div>

        <form id="editRestaurantForm"
              class="space-y-6 bg-white p-8 rounded-2xl border shadow-lg transition">

            <input type="hidden" id="editId" value="{{ $restaurant->id }}">

            {{-- Basic Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="font-semibold block">Restaurant Adı</label>
                    <input type="text" id="editName" value="{{ $restaurant->name }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:border-blue-400">
                </div>
                <div>
                    <label class="font-semibold block">Website</label>
                    <input type="text" id="editWebsite" value="{{ $restaurant->website }}"
                           class="w-full border rounded-lg px-3 py-2 focus:ring focus:border-blue-400">
                </div>
            </div>
            <div class="mt-8 flex items-center gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 font-medium shadow">
                    Güncelle
                </button>

                <a href="/restaurants"
                   class="bg-gray-600 text-white px-8 py-2 rounded-lg hover:bg-gray-700 font-medium shadow">
                    Geri
                </a>
            </div>

        </form>

            {{-- Providers --}}
            <h3 class="text-xl font-semibold mt-8 mb-4">Provider Entegrasyonları</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">

                {{-- Provider Card Component --}}
                @php
                    $providers = [
                        [
                            'name' => 'Getir',
                            'logo' => 'https://restoran.getiryemek.com/assets/img/bimutluluk.png',
                            'fields' => [
                                ['label'=>'Restaurant ID', 'id'=>'getirRestaurantId', 'value'=>$getir['restaurantId'] ?? ''],
                                ['label'=>'Secret Key', 'id'=>'getirSecretKey', 'value'=>$getir['secretKey'] ?? ''],
                            ]
                        ],
                        [
                            'name' => 'Yemeksepeti',
                            'logo' => 'https://www.yeppos.com/wp-content/uploads/2023/06/yemeksepeti_plus_shadow-1024x888.png',
                            'fields' => [
                                ['label'=>'Restaurant ID', 'id'=>'ysRestaurantId', 'value'=>$ys['restaurantId'] ?? ''],
                                ['label'=>'Chain ID', 'id'=>'ysChainId', 'value'=>$ys['chainId'] ?? ''],
                            ]
                        ],
                        [
                            'name' => 'Migros Yemek',
                            'logo' => 'https://www.restoant.com/sites/default/files/img/brands/my.png',
                            'fields' => [
                                ['label'=>'Restaurant ID', 'id'=>'migrosRestaurantId', 'value'=>$migros['restaurantId'] ?? ''],
                                ['label'=>'Chain ID', 'id'=>'migrosChainId', 'value'=>$migros['chainId'] ?? ''],
                                ['label'=>'API Key', 'id'=>'migrosApiKey', 'value'=>$migros['apiKey'] ?? ''],
                            ]
                        ],
                        [
                            'name' => 'Trendyol Yemek',
                            'logo' => 'https://files.sikayetvar.com/lg/cmp/20/208487.png?1747720934',
                            'fields' => [
                                ['label'=>'Restaurant ID', 'id'=>'trendyolRestaurantId', 'value'=>$trendyol['restaurantId'] ?? ''],
                                ['label'=>'API Key', 'id'=>'trendyolApiKey', 'value'=>$trendyol['apiKey'] ?? ''],
                                ['label'=>'API Secret Key', 'id'=>'trendyolApiSecretKey', 'value'=>$trendyol['apiSecretKey'] ?? ''],
                                ['label'=>'Supplier ID', 'id'=>'trendyolSupplierId', 'value'=>$trendyol['supplierId'] ?? ''],
                            ]
                        ],
                    ];
                @endphp

                @foreach($providers as $p)
                   <div class="bg-white shadow-sm py-2 px-2 space-y-2">
                       <form class="p-5 border rounded-xl shadow-sm hover:shadow-md transition bg-gray-50">
                           @csrf
                           <div class="flex items-center gap-2 mb-4">
                               <img src="{{ $p['logo'] }}" class="h-5" alt="provider logo">
                               <p class="font-semibold text-lg">{{ $p['name'] }}</p>
                           </div>

                           @foreach($p['fields'] as $field)
                               <label class="block font-medium text-sm">{{ $field['label'] }}</label>
                               <input type="text" id="{{ $field['id'] }}" value="{{ $field['value'] }}"
                                      class="w-full border rounded-lg px-2 py-1 mb-2 focus:ring focus:border-blue-400">
                           @endforeach
                           <button class="bg-blue-600 text-white py-2 px-2 rounded-lg ">Kaydet</button>
                       </form>
                   </div>
                @endforeach
            </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const alertBox = document.getElementById('alertBox');

            function showAlert(message, type = 'success') {
                alertBox.textContent = message;
                alertBox.className = '';
                alertBox.classList.add('mb-4', 'p-3', 'rounded', 'text-white');
                if(type === 'success') {
                    alertBox.classList.add('bg-green-500');
                } else {
                    alertBox.classList.add('bg-red-500');
                }
                alertBox.classList.remove('hidden');
                setTimeout(() => alertBox.classList.add('hidden'), 5000);
            }

            // Ana Restaurant Form
            const restaurantForm = document.getElementById('editRestaurantForm');
            restaurantForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const id = document.getElementById('editId').value;
                const payload = {
                    name: document.getElementById('editName').value,
                    website: document.getElementById('editWebsite').value
                };

                try {
                    const res = await fetch(`/restaurants/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if(res.ok) showAlert(data.message, 'success');
                    else showAlert(data.message || 'Bir hata oluştu', 'error');
                } catch(err) {
                    showAlert('Sunucu hatası', 'error');
                }
            });

            // Provider Formları
            const providerForms = document.querySelectorAll('form.p-5');
            providerForms.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const id = document.getElementById('editId').value;

                    // Form içindeki tüm inputları al
                    const inputs = form.querySelectorAll('input');
                    const providerData = {};
                    inputs.forEach(input => {
                        providerData[input.id.replace(/^(getir|ys|migros|trendyol)/, '')] = input.value || null;
                    });

                    // Hangi provider olduğunu belirle
                    let providerKey = '';
                    if(form.querySelector('#getirRestaurantId')) providerKey = 'getir';
                    else if(form.querySelector('#ysRestaurantId')) providerKey = 'yemeksepeti';
                    else if(form.querySelector('#migrosRestaurantId')) providerKey = 'migros';
                    else if(form.querySelector('#trendyolRestaurantId')) providerKey = 'trendyol';

                    const payload = {};
                    payload[providerKey] = providerData;

                    try {
                        const res = await fetch(`/restaurants/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if(res.ok) showAlert(`${providerKey.toUpperCase()} başarıyla güncellendi`, 'success');
                        else showAlert(data.message || 'Bir hata oluştu', 'error');
                    } catch(err) {
                        showAlert('Sunucu hatası', 'error');
                    }
                });
            });

        });
    </script>
@endsection


