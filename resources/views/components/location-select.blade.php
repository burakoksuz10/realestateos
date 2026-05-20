@props([
    'city' => '',
    'district' => '',
    'cityRequired' => false,
    'districtRequired' => false,
    'uid' => 'loc',
])

@php
    $cities = config('locations.cities', []);
    $currentDistricts = isset($cities[$city]) ? $cities[$city] : [];
@endphp

<div>
    <label class="block text-sm font-medium text-dark-300 mb-2">İl {{ $cityRequired ? '*' : '' }}</label>
    <select name="city" id="city_{{ $uid }}" {{ $cityRequired ? 'required' : '' }}
        onchange="updateDistricts_{{ $uid }}(this.value)"
        class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option value="">Seçiniz</option>
        @foreach(array_keys($cities) as $c)
            <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-dark-300 mb-2">İlçe {{ $districtRequired ? '*' : '' }}</label>
    <select name="district" id="district_{{ $uid }}" {{ $districtRequired ? 'required' : '' }}
        class="w-full px-4 py-2.5 bg-dark-800 border border-dark-700 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
        <option value="">Seçiniz</option>
        @foreach($currentDistricts as $d)
            <option value="{{ $d }}" {{ $district === $d ? 'selected' : '' }}>{{ $d }}</option>
        @endforeach
    </select>
</div>

<script>
var locationData_{{ $uid }} = @json($cities);

function updateDistricts_{{ $uid }}(city) {
    var select = document.getElementById('district_{{ $uid }}');
    var districts = locationData_{{ $uid }}[city] || [];
    select.innerHTML = '<option value="">Seçiniz</option>';
    districts.forEach(function(d) {
        var opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        select.appendChild(opt);
    });
}
</script>
