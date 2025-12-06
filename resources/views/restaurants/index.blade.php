@extends('layouts.app')
@section('content')

    <div class="container mx-auto p-6">

        {{-- FLASH MESSAGE --}}
        <div id="alertBox" class="hidden mb-4 p-3 rounded text-white"></div>

        {{-- ================= RESTAURANT EKLE ================= --}}
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Restaurant Ekle</h2>

            <form id="restaurantForm" class="space-y-3">
                <input type="text" id="name" placeholder="Restaurant Adı"
                       class="w-full border rounded px-3 py-2" required>

                <input type="text" id="website" placeholder="Website (Opsiyonel)"
                       class="w-full border rounded px-3 py-2">

                <button type="submit"
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Ekle
                </button>
            </form>
        </div>

        {{-- ================= RESTAURANT LİSTE ================= --}}
        <h2 class="text-xl font-semibold mb-3">Restaurant Listesi</h2>
        <table class="min-w-full bg-white border rounded shadow" id="restaurantTable">
            <thead class="bg-gray-200">
            <tr>
                <th class="p-3 border">Name</th>
                <th class="p-3 border">Website</th>
                <th class="p-3 border">İşlemler</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>

        function showAlert(type, msg) {
            const alert = document.getElementById("alertBox");
            alert.className = "mb-4 p-3 rounded text-white " + (type === "success" ? "bg-green-500" : "bg-red-500");
            alert.innerText = msg;
            alert.classList.remove("hidden");
            setTimeout(() => alert.classList.add("hidden"), 2500);
        }

        // CREATE
        document.getElementById("restaurantForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            try {
                await axios.post("/restaurants", {
                    name: document.getElementById("name").value,
                    website: document.getElementById("website").value
                });
                showAlert("success", "Restaurant başarıyla eklendi!");
                document.getElementById("restaurantForm").reset();
                loadTable();
            } catch (error) {
                showAlert("error", "Hata oluştu!");
            }
        });

        function editPage(id){
            window.location.href = `/restaurants/${id}`
        }

        // READ TABLE
        async function loadTable() {
            try {
                const res = await axios.get("/restaurants");
                const tbody = document.querySelector("#restaurantTable tbody");
                tbody.innerHTML = "";

                if (res.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">Restaurant bulunamadı</td></tr>`;
                    return;
                }

                res.data.forEach(r => {
                    tbody.innerHTML += `
                <tr>
                    <td class="p-3 border">${r.name}</td>
                    <td class="p-3 border">${r.website ?? "-"}</td>
                    <td class="p-3 border space-x-2">
                        <button onclick='editPage(${r.id})'
                            class="bg-blue-500 text-white px-2 py-1 rounded">Edit</button>
                        <button onclick='deleteRestaurant(${r.id})'
                            class="bg-red-500 text-white px-2 py-1 rounded">Sil</button>
                    </td>
                </tr>
            `;
                });

            } catch (error) {
                showAlert("error", "Veri alınamadı!");
            }
        }
        loadTable();

        // DELETE
        async function deleteRestaurant(id) {
            if (!confirm("Silmek istediğinize emin misiniz?")) return;
            try {
                await axios.delete(`/restaurants/${id}`);
                showAlert("success", "Silindi!");
                loadTable();
            } catch {
                showAlert("error", "Silinemedi!");
            }
        }
    </script>
@endsection
