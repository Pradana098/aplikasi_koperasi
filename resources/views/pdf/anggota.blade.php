<!DOCTYPE html>
<html>
<head>
    <title>Data Anggota</title>
    <style>
        table {
            width: 100%; border-collapse: collapse;
        }
        th, td {
            border: 1px solid black; padding: 8px; text-align: left;
        }
    </style>
</head>
<body>
    <h2>Data Anggota</h2>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>No Telepon</th>
                <th>NIP</th>
                <th>Tempat & Tanggal Lahir</th>
                <th>Alamat</th>
                <th>Unit Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anggota as $a)
            <tr>
                <td>{{ $a->nama }}</td>
                <td>{{ $a->no_telepon }}</td>
                <td>{{ $a->nip }}</td>
                <td>{{ $a->tempat_lahir }}, {{ $a->tanggal_lahir }}</td>
                <td>{{ $a->alamat_rumah }}</td>
                <td>{{ $a->unit_kerja }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
