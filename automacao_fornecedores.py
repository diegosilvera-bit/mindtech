from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time

fake = Faker('pt_BR')
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

# Tempo de pausa entre cada etapa (0.5 segundos é o ideal para o olho humano acompanhar)
VELOCIDADE = 0.5

try:
    # 1. LOGIN
    driver.get("http://localhost/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # 2. ABRE A TELA
    driver.get("http://localhost/mindtech/fornecedores/cadastrar.php")

    # 3. PREENCHIMENTO RÁPIDO COM VISUALIZAÇÃO
    campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
    
    # Preenche Nome
    campo_nome.send_keys(fake.company())
    time.sleep(VELOCIDADE)

    # Preenche CNPJ
    driver.find_element(By.NAME, 'cnpj').send_keys(fake.cnpj())
    time.sleep(VELOCIDADE)

    # Preenche E-mail
    driver.find_element(By.NAME, 'email').send_keys(fake.company_email())
    time.sleep(VELOCIDADE)

    # Preenche Telefone
    driver.find_element(By.NAME, 'telefone').send_keys(fake.cellphone_number())
    time.sleep(VELOCIDADE)

    # 4. SALVAR
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    print("Fornecedor cadastrado com sucesso!")
    
    # Pausa final de 2 segundos só para ver a mensagem de sucesso verde
    time.sleep(2)

except Exception as e:
    print(f"Ocorreu um erro: {e}")

finally:
    driver.quit()