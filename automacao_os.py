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

# Velocidade equilibrada para acompanhamento visual (0.5 segundos)
VELOCIDADE = 0.5 

try:
    # ==========================================
    # PASSO 1: LOGIN NO SISTEMA
    # ==========================================
    driver.get("http://localhost/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 2: ACESSAR A TELA DE ABRIR O.S.
    # ==========================================
    # Ajuste a URL para a pasta correta do seu sistema
    driver.get("http://localhost/mindtech/ordens_servico/cadastrar.php")

    # ==========================================
    # PASSO 3: SELECIONAR CLIENTE (TOMSELECT 1)
    # ==========================================
    # Clica especificamente no TomSelect do Cliente
    ts_cliente = wait.until(EC.element_to_be_clickable(
        (By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]")
    ))
    ts_cliente.click()
    time.sleep(0.3)
    
    # Seleciona o primeiro cliente da lista via teclado
    input_ts_cliente = driver.find_element(By.XPATH, "//select[@id='id_cliente']/following-sibling::div//input")
    input_ts_cliente.send_keys(Keys.ARROW_DOWN)
    input_ts_cliente.send_keys(Keys.ENTER)
    
    # Pausa crucial para permitir que a função JS 'filtrarEquipamentos()' popule os aparelhos do cliente!
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 4: SELECIONAR APARELHO (TOMSELECT 2)
    # ==========================================
    ts_equipamento = driver.find_element(
        By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'ts-control')]"
    )
    ts_equipamento.click()
    time.sleep(0.3)
    
    input_ts_equip = driver.find_element(By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//input")
    input_ts_equip.send_keys(Keys.ARROW_DOWN)
    input_ts_equip.send_keys(Keys.ENTER)
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 5: SELECIONAR TÉCNICO (TOMSELECT 3)
    # ==========================================
    ts_tecnico = driver.find_element(
        By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//div[contains(@class, 'ts-control')]"
    )
    ts_tecnico.click()
    time.sleep(0.3)
    
    input_ts_tec = driver.find_element(By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//input")
    input_ts_tec.send_keys(Keys.ARROW_DOWN)
    input_ts_tec.send_keys(Keys.ENTER)
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 6: STATUS INICIAL E PREVISÃO DE ENTREGA
    # ==========================================
    # Seleciona o Status (Em Análise)
    select_status = Select(driver.find_element(By.NAME, 'status'))
    select_status.select_by_value("EM_ANALISE")
    time.sleep(VELOCIDADE)

    # Define uma data de previsão para daqui a 5 dias (no formato DDMMYYYY)
    data_futura = (datetime.now() + timedelta(days=5)).strftime("%d%m%Y")
    campo_data = driver.find_element(By.NAME, 'data_prevista_entrega')
    campo_data.send_keys(data_futura)
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 7: DESCREVER O PROBLEMA RELATADO
    # ==========================================
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

    # ==========================================
    # PASSO 8: GRAVAR A O.S.
    # ==========================================
    btn_salvar = driver.find_element(By.XPATH, "//button[@type='submit']")
    btn_salvar.click()
    
    print("Ordem de Serviço criada com sucesso!")
    time.sleep(3)

except Exception as e:
    print(f"Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()