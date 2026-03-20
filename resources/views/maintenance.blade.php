@extends(admin.AdminOne.layout.assets')

@section('title', 'Maintenance')

@section('login')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <div style="
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: #f2f4f8;
        font-family: 'Poppins', sans-serif;
    ">
        <div style="
            background: #fff;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        ">
            <img src="{{url('/image/maintenance.jpg')}}" alt="Maintenance" style="width: 150px; margin-bottom: 30px;">
            <h1 style="font-size: 32px; color: #e63946; margin-bottom: 20px;">⛔ Maintenance Mode</h1>
            <p style="font-size: 18px; color: #555;">
                Kami sedang melakukan pemeliharaan sistem untuk peningkatan performa dan layanan.
            </p>
            <p style="font-size: 18px; color: #555; margin-top: 10px;">
                Silakan kembali beberapa saat lagi. Terima kasih atas pengertiannya.
            </p>
        </div>
    </div>
@endsection
