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
    padding-top: 56px !important; /* Empurra o conteúdo para não ficar escondido debaixo da barra fixa do topo */
}

/* =======================================================
   BARRA DE ROLAGEM SLIM (GLOBAL)
   ======================================================= */
* {
    scrollbar-width: thin; /* Deixa a barra fininha no Firefox */
    scrollbar-color: #333333 #121212; /* Cor da barra e cor do fundo */
}

::-webkit-scrollbar {
    width: 6px; /* Largura da barra fininha (Chrome, Safari, Edge) */
    height: 6px; 
}
::-webkit-scrollbar-track {
    background: #121212; 
}
::-webkit-scrollbar-thumb {
    background-color: #333333; 
    border-radius: 10px; 
}
::-webkit-scrollbar-thumb:hover {
    background-color: #ecc245; /* Brilha em dourado ao passar o rato! */
}

/* 2. Força Títulos Principais a ficarem Brancos */
h1, h2, h3, h4, h5, h6, .navbar-brand {
    color: #ffffff !important;
}

/* Ajuste da Barra de Navegação Superior FIXA */
.navbar {
    background-color: #161616 !important;
    border-bottom: 1px solid #2d2d2d !important;
    position: fixed !important; /* Trava a barra no topo */
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1050; /* Fica por cima de tudo */
}
.navbar-brand {
    color: #ecc245 !important; /* Destaca o nome MindTech em Dourado */
    font-weight: bold;
    text-decoration: none; /* Remove o sublinhado padrão de links */
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

/* 4. REMOVE O CONTORNO AZUL DE FOCO DOS CAMPOS E BOTÕES (+ DOURADO) */
.form-control:focus, 
.form-select:focus, 
.btn:focus {
    border-color: #ecc245 !important;
    box-shadow: 0 0 0 0.25rem rgba(236, 194, 69, 0.25) !important;
    outline: none !important;
}
</style>
</head>
<body>
<nav class="navbar navbar-dark">
  <div class="container-fluid">
    <a href="/mindtech/dashboard/index.php" class="navbar-brand">MINDTECH</a>
    <a href="/mindtech/logout.php" class="btn btn-light btn-sm">Sair</a>
  </div>
</nav>