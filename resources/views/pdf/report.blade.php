<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Uang Keluar</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 70%;
            margin: auto;
        }

        th,
        td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }
    </style>
</head>

<body>
    <div style="flex-direction: column;">
        <div>
            <h1 style="text-align: center;">{{ $title }}</h1>
       </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Deskripsi</th>
                        <th>Tujuan Pengeluaran</th>
                        <th>Jumlah Uang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $item)
                        <tr>
                            <td>{{ $item->name}}</td>
                            <td>{{ $item->deskripsi}}</td>
                            <td>{{ $item->purposable->name ?? '-' }}</td>
                            <td>{{ $item->amount}}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align: center;">Jumlah Total</td>
                        <td>{{ $totalAmount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>




</body>

</html>