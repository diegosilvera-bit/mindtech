<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acompanhar Reparo - MindTech</title>
    <!-- Usando a versão estável e recomendada do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Fundo dark corporativo e elegante */
        body {
            background-color: #0c0c0e !important;
            color: #f5f7f9 !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        /* Card premium centralizado */
        .cliente-card {
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            background-color: #16161a;
            border: 1px solid #2d2d35;
            max-width: 450px;
            width: 100%;
            position: relative;
            overflow: hidden;
            padding: 2.5rem !important;
        }

        /* Faixa sutil dourada no topo para dar identidade premium */
        .cliente-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ecc245, #d6ae3b);
        }

        /* Container circular do logotipo para disfarçar o fundo quadrado original */
        .brand-logo-container {
            width: 130px;
            height: 130px;
            margin: 0 auto 1.5rem auto;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(236, 194, 69, 0.25);
            background-color: #000000; /* Fundo preto para mesclar perfeitamente com o logo1.png */
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .brand-logo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Badge circular perfeito para o ícone de busca */
        .icon-search-badge {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(236, 194, 69, 0.08);
            color: #ecc245;
            border: 1px solid rgba(236, 194, 69, 0.2);
            font-size: 1.65rem;
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
        }

        .cliente-card:hover .icon-search-badge {
            background-color: rgba(236, 194, 69, 0.15);
            transform: scale(1.05);
        }

        /* Botão premium de alta conversão */
        .btn-action {
            background-color: #ecc245;
            color: #121214;
            font-weight: 700;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 15px rgba(236, 194, 69, 0.15);
        }

        .btn-action:hover {
            background-color: #d6ae3b;
            color: #121214;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 194, 69, 0.3);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        /* Link de voltar polido */
        .back-link {
            color: #a0a0a5;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #ecc245;
        }
    </style>
</head>

<body class="p-3">

    <div class="card cliente-card text-center">
        <!-- Cabeçalho / Logo integrado -->
        <div class="brand-logo-container">
            <img src="assets/img/logo1.png" alt="MindTech Logo" class="brand-logo">
        </div>

        <div class="mb-4">
            <!-- Ícone estilizado e perfeitamente circular -->
            <div class="icon-search-badge">
                <i class="bi bi-search"></i>
            </div>
            
            <h3 class="fw-bold text-white mb-2">Acompanhar Reparo</h3>
            <p class="text-secondary small px-2 mb-0">
                Deixou o seu aparelho conosco? Consulte agora o andamento do seu reparo de forma rápida, segura e sem complicação.
            </p>
        </div>

        <!-- Botão principal ajustado e refinado -->
        <div class="mb-4">
            <a href="ordens_servico/consultar.php" class="btn btn-action w-100 fs-6 py-2_5 d-flex align-items-center justify-content-center gap-2">
                <span>Consultar Meu Aparelho</span>
                <i class="bi bi-arrow-right fs-5"></i>
            </a>
        </div>

        <!-- Rodapé de segurança -->
        <div class="small text-secondary opacity-75 mb-4 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-shield-check text-success fs-5"></i>
            <span>Consulta 100% segura</span>
        </div>

        <!-- Link de navegação -->
        <div class="pt-3 border-top border-secondary border-opacity-20">
            <a href="TelaInicial.html" class="back-link text-decoration-none d-inline-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar à tela inicial</span>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>