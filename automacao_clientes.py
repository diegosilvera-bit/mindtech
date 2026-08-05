from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
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
    URL_LOGIN = "http://localhost:8080/mindtech/login.php" 
    driver.get(URL_LOGIN)
    
    campo_login = wait.until(EC.visibility_of_element_located((By.NAME, 'login')))
    campo_login.send_keys('admin') 
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Cadastro de Cliente {i} de {qtd_testes} ---")

        URL_CADASTRO = "http://localhost:8080/mindtech/clientes/cadastrar.php" 
        driver.get(URL_CADASTRO)

        # 1. Preencher Nome
        campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
        campo_nome.send_keys(fake.name())
        time.sleep(VELOCIDADE)

        # 2. Preencher Data de Nascimento
        data_falsa = fake.date_of_birth(minimum_age=18, maximum_age=80).strftime('%d/%m/%Y')
        driver.find_element(By.NAME, 'data_nascimento').send_keys(data_falsa)
        time.sleep(VELOCIDADE)

        # 3. Preencher CPF e RG
        driver.find_element(By.NAME, 'cpf').send_keys(fake.cpf())
        time.sleep(VELOCIDADE)

        driver.find_element(By.NAME, 'rg').send_keys(fake.rg())
        time.sleep(VELOCIDADE)

        # 4. Preencher Telefone
        driver.find_element(By.NAME, 'telefone').send_keys(fake.cellphone_number())
        time.sleep(VELOCIDADE)

        # 5. Preencher Endereço
        driver.find_element(By.NAME, 'endereco').send_keys(fake.street_address())
        time.sleep(VELOCIDADE)

        # 6. Clicar no botão Salvar
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        print(f"✅ Cliente {i} cadastrado com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os cadastros de clientes foram finalizados com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro: {e}")

finally:
    driver.quit()