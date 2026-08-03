from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
import time
import random

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

# Tempo de pausa entre as etapas para você conseguir visualizar
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
    # PASSO 2: ACESSAR A TELA DE ORÇAMENTO
    # ==========================================
    # Ajuste a URL para a pasta correta onde fica o orçamento
    driver.get("http://localhost/mindtech/orcamentos/cadastrar.php")

    # ==========================================
    # PASSO 3: SELECIONAR A O.S. EM ABERTO
    # ==========================================
    select_os_elem = wait.until(EC.visibility_of_element_located((By.NAME, 'id_os')))
    select_os = Select(select_os_elem)
    
    # Verifica se existem Ordens de Serviço na lista (excluindo o placeholder)
    if len(select_os.options) > 1:
        select_os.select_by_index(1) # Seleciona a primeira O.S. disponível
        time.sleep(VELOCIDADE)
    else:
        print("Aviso: Nenhuma Ordem de Serviço em aberto disponível para orçamento.")

    # ==========================================
    # PASSO 4: ADICIONAR PEÇA DINAMICAMENTE
    # ==========================================
    select_peca_elem = driver.find_element(By.ID, 'select_peca')
    select_peca = Select(select_peca_elem)
    
    # Seleciona uma peça se houver opções no banco
    if len(select_peca.options) > 1:
        select_peca.select_by_index(1)
        time.sleep(VELOCIDADE)
        
        # Altera a quantidade da peça
        campo_qtd = driver.find_element(By.ID, 'qtd_peca')
        campo_qtd.clear()
        campo_qtd.send_keys('2')
        time.sleep(VELOCIDADE)
        
        # Clica no botão "+ Add" para inserir a peça na tabela JavaScript
        btn_add_peca = driver.find_element(By.XPATH, "//button[contains(text(), '+ Add')]")
        btn_add_peca.click()
        time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 5: INFORMAR MÃO DE OBRA
    # ==========================================
    campo_mao_obra = driver.find_element(By.ID, 'valor_mao_obra')
    campo_mao_obra.clear()
    
    # Digita o valor. A máscara 'mascaraMoeda' converterá 15000 para R$ 150,00
    valor_mao_obra_falso = str(random.choice([8000, 12000, 15000, 20000]))
    campo_mao_obra.send_keys(valor_mao_obra_falso)
    time.sleep(VELOCIDADE)

    # ==========================================
    # PASSO 6: GRAVAR O ORÇAMENTO
    # ==========================================
    btn_gravar = driver.find_element(By.XPATH, "//button[@type='submit']")
    btn_gravar.click()
    
    print("Orçamento gerado com sucesso!")
    time.sleep(2)

except Exception as e:
    print(f"Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()