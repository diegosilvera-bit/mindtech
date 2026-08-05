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
    # PASSO 1: LOGIN NO SISTEMA (Apenas 1 vez no início)
    driver.get("http://localhost:8080/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Gerar Orçamento {i} de {qtd_testes} ---")

        # Abre a tela de cadastro de orçamento
        driver.get("http://localhost:8080/mindtech/orcamentos/cadastrar.php")

        # 1. Selecionar a próxima O.S. em aberto
        select_os_elem = wait.until(EC.visibility_of_element_located((By.NAME, 'id_os')))
        select_os = Select(select_os_elem)
        
        # Verifica se existem Ordens de Serviço disponíveis
        if len(select_os.options) <= 1:
            print("⚠️ Nenhuma Ordem de Serviço em aberto disponível para orçamento. Finalizando os testes.")
            break

        select_os.select_by_index(1) # Seleciona a primeira O.S. disponível da lista
        time.sleep(VELOCIDADE)

        # 2. Adicionar Peça Dinamicamente
        select_peca_elem = driver.find_element(By.ID, 'select_peca')
        select_peca = Select(select_peca_elem)
        
        if len(select_peca.options) > 1:
            select_peca.select_by_index(1)
            time.sleep(VELOCIDADE)
            
            # Define a quantidade da peça
            campo_qtd = driver.find_element(By.ID, 'qtd_peca')
            campo_qtd.clear()
            campo_qtd.send_keys('2')
            time.sleep(VELOCIDADE)
            
            # Clica no botão "+ Add"
            btn_add_peca = driver.find_element(By.XPATH, "//button[contains(text(), '+ Add')]")
            btn_add_peca.click()
            time.sleep(VELOCIDADE)

        # 3. Informar Mão de Obra
        campo_mao_obra = driver.find_element(By.ID, 'valor_mao_obra')
        campo_mao_obra.clear()
        
        # Digita o valor (a máscara 'mascaraMoeda' converte para formato de moeda R$)
        valor_mao_obra_falso = str(random.choice([8000, 12000, 15000, 20000]))
        campo_mao_obra.send_keys(valor_mao_obra_falso)
        time.sleep(VELOCIDADE)

        # 4. Gravar o Orçamento
        btn_gravar = driver.find_element(By.XPATH, "//button[@type='submit']")
        btn_gravar.click()
        
        print(f"✅ Orçamento {i} gerado com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os testes de orçamento foram concluídos!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()