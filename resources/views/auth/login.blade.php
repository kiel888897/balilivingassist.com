<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin sign in | BLA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #172b3a;
            --muted: #60717c;
            --teal: #087e79;
            --teal-dark: #066963;
            --paper: #f3f6f5;
            --line: #dce5e2;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            grid-template-columns: minmax(280px, 0.88fr) minmax(420px, 1.12fr);
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background: var(--paper);
        }

        .brand-side {
            min-height: 100vh;
            padding: 42px clamp(28px, 5vw, 76px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: white;
            background: #173d46;
        }

        .brand {
            display: flex;
            color: white !important;
            align-items: center;
            gap: 12px;
            font: 800 18px 'Manrope', sans-serif;
        }

        .mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .45);
            border-radius: 10px;
        }

        .brand-copy {
            max-width: 440px;
            padding-bottom: 10vh;
        }

        .eyebrow {
            color: #a8dbca;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 16px 0;
            font: 800 clamp(32px, 4vw, 52px)/1.08 'Manrope', sans-serif;
        }

        .brand-copy p {
            max-width: 390px;
            color: #d3e1df;
            line-height: 1.7;
        }

        .side-foot {
            color: #b8ccca;
            font-size: 13px;
        }

        .form-side {
            display: grid;
            place-items: center;
            padding: 36px 24px;
        }

        .form-wrap {
            width: min(420px, 100%);
        }

        .form-wrap h2 {
            margin: 0;
            font: 800 28px 'Manrope', sans-serif;
        }

        .intro {
            margin: 8px 0 28px;
            color: var(--muted);
        }

        .field {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
        }

        label {
            font-size: 14px;
            font-weight: 700;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            min-height: 48px;
            border: 1px solid #c9d5d1;
            border-radius: 8px;
            padding: 0 13px;
            background: white;
            color: var(--ink);
            font: inherit;
        }

        input:focus {
            outline: 3px solid rgba(8, 126, 121, .18);
            border-color: var(--teal);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--muted);
            font-size: 14px;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--teal);
        }

        .error {
            margin-top: 5px;
            color: var(--danger);
            font-size: 13px;
        }

        button {
            width: 100%;
            min-height: 48px;
            margin-top: 24px;
            border: 0;
            border-radius: 8px;
            background: var(--teal);
            color: white;
            font: 700 15px 'DM Sans', sans-serif;
            cursor: pointer;
        }

        button:hover {
            background: var(--teal-dark);
        }

        body.admin-login .form-wrap form button[type="submit"] {
            border: 1px solid var(--teal-dark);
            background-color: var(--teal);
            box-shadow: 0 4px 12px rgba(6, 105, 99, .2);
        }

        body.admin-login .form-wrap form button[type="submit"]:hover {
            background-color: var(--teal-dark);
        }

        body.admin-login .form-wrap form button[type="submit"]:focus-visible {
            outline: 3px solid rgba(8, 126, 121, .3);
            outline-offset: 3px;
        }

        .back {
            display: block;
            margin-top: 22px;
            color: var(--muted);
            text-align: center;
            font-size: 14px;
            text-decoration: none;
        }

        @media (max-width: 760px) {
            body {
                grid-template-columns: 1fr;
            }

            .brand-side {
                min-height: auto;
                padding: 22px 24px;
            }

            .brand-copy {
                padding: 36px 0 8px;
            }

            .brand-copy h1 {
                font-size: 34px;
            }

            .brand-copy p,
            .side-foot {
                display: none;
            }

            .form-side {
                padding: 38px 24px;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
</head>

<body class="admin-login">
    <aside class="brand-side">
        <div class="brand"><span class="mark">B</span><span>Bali Living Assist</span></div>
        <div class="brand-copy">
            <div class="eyebrow">Operations portal</div>
            <h1>Manage BLA from one place.</h1>
            <p>Admin access for the product catalog and daily operations.</p>
        </div>
        <div class="side-foot">Bali Living Assist · Admin</div>
    </aside>

    <main class="form-side">
        <div class="form-wrap">
            <h2>Admin sign in</h2>
            <p class="intro">Masuk menggunakan akun admin Anda.</p>

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <div class="error" role="alert">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    @error('password') <div class="error" role="alert">{{ $message }}</div> @enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1">
                    Ingat saya
                </label>

                <button type="submit">Masuk ke panel</button>
            </form>

            <a class="back" href="{{ route('home') }}">Kembali ke situs BLA</a>
        </div>
    </main>
</body>

</html>