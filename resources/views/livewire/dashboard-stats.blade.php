<div class="grid grid-cols-2 gap-3 p-4 md:grid-cols-4 md:p-6">
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</p>
        <p class="text-sm text-gray-500">মোট ছাত্র</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-2xl font-bold text-gray-900">{{ $totalTeachers }}</p>
        <p class="text-sm text-gray-500">মোট শিক্ষক</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-2xl font-bold {{ $attendanceRate !== null && $attendanceRate < 80 ? 'text-amber-600' : 'text-green-600' }}">
            {{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}
        </p>
        <p class="text-sm text-gray-500">আজকের উপস্থিতি</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <p class="text-2xl font-bold text-gray-900">৳{{ number_format($monthCollection, 0) }}</p>
        <p class="text-sm text-gray-500">এই মাসের কালেকশন</p>
    </div>
</div>
