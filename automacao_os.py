from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
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
    qtd_testes = int(input("Quantos testes gostaria de cadastrar? "))
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
        print(f"\n--- Executando Abertura de O.S. {i} de {qtd_testes} ---")

        # Acesse a tela de abertura de O.S.
        driver.get("http://localhost:8080/mindtech/ordens_servico/cadastrar.php")

        # 1. Selecionar Cliente (TomSelect 1)
        ts_cliente = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]")
        ))
        ts_cliente.click()
        time.sleep(0.3)
        
        input_ts_cliente = driver.find_element(By.XPATH, "//select[@id='id_cliente']/following-sibling::div//input")
        input_ts_cliente.send_keys(Keys.ARROW_DOWN)
        input_ts_cliente.send_keys(Keys.ENTER)
        time.sleep(VELOCIDADE)

        # 2. Selecionar Aparelho (TomSelect 2)
        ts_equipamento = driver.find_element(
            By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'ts-control')]"
        )
        ts_equipamento.click()
        time.sleep(0.3)
        
        input_ts_equip = driver.find_element(By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//input")
        input_ts_equip.send_keys(Keys.ARROW_DOWN)
        input_ts_equip.send_keys(Keys.ENTER)
        time.sleep(VELOCIDADE)

        # 3. Selecionar Técnico (TomSelect 3)
        ts_tecnico = driver.find_element(
            By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//div[contains(@class, 'ts-control')]"
        )
        ts_tecnico.click()
        time.sleep(0.3)
        
        input_ts_tec = driver.find_element(By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//input")
        input_ts_tec.send_keys(Keys.ARROW_DOWN)
        input_ts_tec.send_keys(Keys.ENTER)
        time.sleep(VELOCIDADE)

        # 4. Status Inicial e Previsão de Entrega
        select_status = Select(driver.find_element(By.NAME, 'status'))
        select_status.select_by_value("EM_ANALISE")
        time.sleep(VELOCIDADE)

        data_futura = (datetime.now() + timedelta(days=5)).strftime("%d%m%Y")
        campo_data = driver.find_element(By.NAME, 'data_prevista_entrega')
        campo_data.send_keys(data_futura)
        time.sleep(VELOCIDADE)

        # 5. Descrever o Problema Relatado
        problemas_comuns = [
            "Aparelho não liga após sofrer uma queda. Cliente solicita orçamento prévio.",
            "Tela trincada com falha no touch. Aparelho liga e emite sons normalmente.",
            "Bateria descarregando muito rápido e esquentando durante o uso.",
            "Conector de carga danificado. Não reconhece o cabo do carregador.",
            "Limpeza preventiva e troca de pasta térmica. Equipamento desligando por superaquecimento."
        ]
        
        campo_obs = driver.find_element(By.NAME, 'observacoes')
        campo_obs.send_keys(random.choice(problemas_comuns))
        time.sleep(VELOCIDADE)

        # 6. Gravar a O.S.
        btn_salvar = driver.find_element(By.XPATH, "//button[@type='submit']")
        btn_salvar.click()
        
        print(f"✅ Ordem de Serviço {i} criada com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todas as Ordens de Serviço foram criadas com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()