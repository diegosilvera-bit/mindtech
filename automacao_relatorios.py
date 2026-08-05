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

# Velocidade de 1 segundo de intervalo entre cada etapa
VELOCIDADE = 1.0 

# ==========================================
# PERGUNTA A QUANTIDADE DE TESTES
# ==========================================
try:
    qtd_testes = int(input("Quantos testes gostaria de executar? "))
except ValueError:
    print("Valor inválido! Executando 1 teste por padrão.")
    qtd_testes = 1

try:
    # PASSO 1: FAZER O LOGIN NO SISTEMA (Apenas 1 vez no início)
    driver.get("http://localhost:8080/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Geração de Relatório {i} de {qtd_testes} ---")

        # Acesse a tela de relatórios
        driver.get("http://localhost:8080/mindtech/relatorios/cadastrar.php")

        # 1. Selecionar o Tipo de Relatório
        select_tipo_elem = wait.until(EC.visibility_of_element_located((By.NAME, 'tipo_relatorio')))
        select_tipo = Select(select_tipo_elem)
        
        opcoes_relatorio = ["faturamento", "ordens_servico", "pecas_baixo_estoque"]
        opcao_escolhida = random.choice(opcoes_relatorio)
        select_tipo.select_by_value(opcao_escolhida)
        time.sleep(VELOCIDADE)

        # 2. Definir o Intervalo de Datas
        hoje = datetime.now()
        inicio = hoje - timedelta(days=30)
        
        data_inicio_str = inicio.strftime("%d%m%Y")
        data_fim_str = hoje.strftime("%d%m%Y")
        
        campo_inicio = driver.find_element(By.NAME, 'data_inicio')
        campo_inicio.send_keys(data_inicio_str)
        time.sleep(VELOCIDADE)

        campo_fim = driver.find_element(By.NAME, 'data_fim')
        campo_fim.send_keys(data_fim_str)
        time.sleep(VELOCIDADE)

        # 3. Gerar Relatório
        btn_gerar = driver.find_element(By.XPATH, "//button[@type='submit']")
        btn_gerar.click()
        
        print(f"✅ Relatório {i} ('{opcao_escolhida}') gerado com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os testes de relatórios foram concluídos com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()