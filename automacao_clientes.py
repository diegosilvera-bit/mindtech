from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time

fake = Faker('pt_BR')
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))

try:
    # PASSO 1: FAZER O LOGIN NO SISTEMA
    URL_LOGIN = "http://localhost:8080/mindtech/login.php" 
    driver.get(URL_LOGIN)
    time.sleep(2)
    
    #  senha e login
    driver.find_element(By.NAME, 'login').send_keys('admin') 
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    
    # Clicar no botão de entrar
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    time.sleep(3) # Aguarda o redirecionamento para a dashboard

    # PASSO 2: ACESSAR A TELA DE CADASTRO
    URL_CADASTRO = "http://localhost:8080/mindtech/clientes/cadastrar.php" 
    driver.get(URL_CADASTRO)
    time.sleep(2)

    # 1. Preencher os dados usando os atributos 'name' mapeados
    driver.find_element(By.NAME, 'nome').send_keys(fake.name())
    
    data_falsa = fake.date_of_birth(minimum_age=18, maximum_age=80).strftime('%d/%m/%Y')
    driver.find_element(By.NAME, 'data_nascimento').send_keys(data_falsa)
    
    # 2. Preencher os campos com máscara
    driver.find_element(By.NAME, 'cpf').send_keys(fake.cpf())
    driver.find_element(By.NAME, 'rg').send_keys(fake.rg())
    
    telefone_falso = fake.cellphone_number()
    driver.find_element(By.NAME, 'telefone').send_keys(telefone_falso)
    
    # 3. Preencher endereço
    driver.find_element(By.NAME, 'endereco').send_keys(fake.street_address())
    
    time.sleep(1) 
    
    # 4. Clicar no botão de Salvar da tela de cadastro
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    print("Login e Cadastro de Cliente realizados com sucesso!")
    time.sleep(3)

except Exception as e:
    print(f"Ocorreu um erro: {e}")

finally:
    driver.quit()