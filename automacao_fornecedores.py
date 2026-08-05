from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time

# 1. PERGUNTA NO TERMINAL (ANTES DE ABRIR O NAVEGADOR)

try:
    qtd_testes = int(input("Quantos testes gostaria de cadastrar? "))
except ValueError:
    print("Valor inválido! Executando 1 teste por padrão.")
    qtd_testes = 1

# 2. INICIALIZAÇÃO DO NAVEGADOR E FERRAMENTAS
fake = Faker('pt_BR')
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

# Velocidade de 1 segundo de intervalo entre cada etapa
VELOCIDADE = 1.0

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
        print(f"\n--- Executando Cadastro de Fornecedor {i} de {qtd_testes} ---")

        # Abre a tela de cadastro
        driver.get("http://localhost:8080/mindtech/fornecedores/cadastrar.php")

        # 1. Preenche Nome
        campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
        campo_nome.send_keys(fake.company())
        time.sleep(VELOCIDADE)

        # 2. Preenche CNPJ
        driver.find_element(By.NAME, 'cnpj').send_keys(fake.cnpj())
        time.sleep(VELOCIDADE)

        # 3. Preenche E-mail
        driver.find_element(By.NAME, 'email').send_keys(fake.company_email())
        time.sleep(VELOCIDADE)

        # 4. Preenche Telefone
        driver.find_element(By.NAME, 'telefone').send_keys(fake.cellphone_number())
        time.sleep(VELOCIDADE)

        # 5. Clicar no botão Salvar
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        print(f"✅ Fornecedor {i} cadastrado com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os cadastros de fornecedores foram finalizados com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro: {e}")

finally:
    driver.quit()