<?php require_once __DIR__ . '/../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MindTech</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/mindtech/assets/css/estilo.css" rel="stylesheet">

<style>
/* 1. Fundo Geral da Página (Escuro Premium) */
body {
    background-color: #121212 !important;
    color: #ffffff !important;
}

/* 2. Força Títulos Principais a ficarem Brancos */
h1, h2, h3, h4, h5, h6, .navbar-brand {
    color: #ffffff !important;
}

/* Ajuste da Barra de Navegação Superior */
.navbar {
    background-color: #161616 !important;
    border-bottom: 1px solid #2d2d2d !important;
}
.navbar-brand {
    color: #ecc245 !important; /* Destaca o nome MindTech em Dourado */
    font-weight: bold;
}

/* 3. CORREÇÃO CRUCIAL PARA AS TABELAS, CARDS E INPUTS DO SISTEMA */
/* Força o texto interno a ficar escuro para dar contraste perfeito com o fundo claro original */
.table, 
.table td, 
.table tr, 
.card-body, 
.form-control, 
.form-select, 
textarea, 
label, 
.form-label {
    color: #212529 !important; /* Cinza bem escuro (padrão legível do Bootstrap) */
}

/* Cabeçalho das tabelas (Fica escuro com as letras em Dourado para dar o destaque premium) */
thead, .table-dark, thead.table-dark th, .table thead th {
    background-color: #161616 !important;
    color: #ecc245 !important;
}

/* Mantém os números e destaques grandes do Dashboard em dourado */
.text-primary, .text-warning, .card-body h1.text-primary, .card-body .fs-1 {
    color: #ecc245 !important;
}

/* Botão Secundário / Sair / Voltar */
.btn-secondary, .btn-light, .btn-sair {
    background-color: #262626 !important;
    border-color: #2d2d2d !important;
    color: #ffffff !important;
}
.btn-secondary:hover, .btn-light:hover {
    background-color: #333333 !important;
    color: #ffffff !important;
}

/* 4. AJUSTES RESPONSIVOS PARA OS LINKS DO MENU */
.navbar-dark .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    font-weight: 500;
    transition: color 0.2s ease-in-out;
}
.navbar-dark .navbar-nav .nav-link:hover,
.navbar-dark .navbar-nav .nav-link.active {
    color: #ecc245 !important; /* Brilha em dourado ao passar o mouse ou estar ativo */
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark py-2">
  <div class="container-fluid">
    <a class="navbar-brand text-uppercase tracking-wider" href="/mindtech/dashboard/index.php">
        <i class="bi bi-cpu-fill me-1"></i> MINDTECH
    </a>
    
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#menuMindTech" aria-controls="menuMindTech" aria-expanded="false" aria-label="Abrir menu de navegação">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="menuMindTech">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 mt-3 mt-lg-0 pt-2 pt-lg-0 border-top border-lg-0 border-secondary border-opacity-25">
        <li class="nav-item">
          <a class="nav-link" href="/mindtech/dashboard/index.php">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/mindtech/os/listar.php">
            <i class="bi bi-tools me-1"></i> Ordens de Serviço
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/mindtech/estoque/listar.php">
            <i class="bi bi-box-seam me-1"></i> Estoque
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/mindtech/clientes/listar.php">
            <i class="bi bi-people me-1"></i> Clientes
          </a>
        </li>
      </ul>
      
      <div class="d-flex mt-3 mt-lg-0 pb-2 pb-lg-0">
        <a href="/mindtech/logout.php" class="btn btn-light btn-sm w-100 w-lg-auto px-3 fw-bold">
          <i class="bi bi-box-arrow-right me-1"></i> Sair
        </a>
      </div>

    </div>
  </div>
</nav>

<div class="container mt-4">