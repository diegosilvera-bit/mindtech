from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from datetime import datetime, timedelta
import time
import random

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

# Tempo de pausa entre as etapas para acompanhamento visual
VELOCIDADE = 1.0

try:
    # PASSO 1: LOGIN NO SISTEMA
    driver.get("http://localhost:8080/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: ACESSAR A TELA DE RELATÓRIOS
    # Ajuste o caminho da pasta caso a tela fique em outro diretório
    driver.get("http://localhost:8080/mindtech/relatorios/cadastrar.php")

    # PASSO 3: SELECIONAR O TIPO DE RELATÓRIO
    select_tipo_elem = wait.until(EC.visibility_of_element_located((By.NAME, 'tipo_relatorio')))
    select_tipo = Select(select_tipo_elem)
    
    # Escolhe aleatoriamente uma das 3 opções de relatório
    opcoes_relatorio = ["faturamento", "ordens_servico", "pecas_baixo_estoque"]
    opcao_escolhida = random.choice(opcoes_relatorio)
    
    select_tipo.select_by_value(opcao_escolhida)
    time.sleep(VELOCIDADE)

    # PASSO 4: DEFINIR O INTERVALO DE DATAS
    # Define início como 30 dias atrás e fim como a data atual
    hoje = datetime.now()
    inicio = hoje - timedelta(days=30)
    
    # Formata como DDMMYYYY (formato padrão para digitação em inputs tipo date no Chrome em PT-BR)
    data_inicio_str = inicio.strftime("%d%m%Y")
    data_fim_str = hoje.strftime("%d%m%Y")
    
    campo_inicio = driver.find_element(By.NAME, 'data_inicio')
    campo_inicio.send_keys(data_inicio_str)
    time.sleep(VELOCIDADE)

    campo_fim = driver.find_element(By.NAME, 'data_fim')
    campo_fim.send_keys(data_fim_str)
    time.sleep(VELOCIDADE)

    # PASSO 5: GERAR RELATÓRIO
    btn_gerar = driver.find_element(By.XPATH, "//button[@type='submit']")
    btn_gerar.click()
    
    print(f"Relatório de '{opcao_escolhida}' gerado com sucesso!")
    
    # Pausa de 3 segundos para visualizar a página de resultados (listar.php)
    time.sleep(3)

except Exception as e:
    print(f"Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()