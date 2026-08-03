from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys # Importação nova para simular teclas do teclado
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time
import random

fake = Faker('pt_BR')
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

try:
    # PASSO 1: FAZER O LOGIN NO SISTEMA
    URL_LOGIN = "http://localhost:8080/mindtech/login.php" 
    driver.get(URL_LOGIN)
    
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    driver.find_element(By.XPATH, "//button[@type='submit']").click()

    # PASSO 2: ACESSAR A TELA DE CADASTRAR PEÇA
    URL_PECAS = "http://localhost:8080/mindtech/estoque/cadastrar.php" 
    driver.get(URL_PECAS)

    # Aguarda o campo 'codigo' carregar na tela
    campo_codigo = wait.until(EC.visibility_of_element_located((By.NAME, 'codigo')))
    
    # 1. Preencher Código e Descrição
    codigo_falso = fake.bothify(text='PC-####-??', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ')
    campo_codigo.send_keys(codigo_falso)
    
    descricoes = [
        "Tela Display LCD iPhone 13 Original",
        "Bateria Samsung Galaxy S21",
        "Teclado Notebook Dell Inspiron 15",
        "Placa Mãe Asus Prime A320M",
        "Memória RAM Corsair 8GB DDR4"
    ]
    driver.find_element(By.NAME, 'descricao').send_keys(random.choice(descricoes))

    # PASSO 3: LIDAR COM O TOMSELECT (FORNECEDOR)
    # Clica na div de controle do TomSelect para abrir o menu
    caixa_tomselect = driver.find_element(By.CSS_SELECTOR, ".ts-control")
    caixa_tomselect.click()
    time.sleep(0.5) # Pausa rápida para a animação do dropdown
    
    # Localiza o input escondido do TomSelect, aperta SETA PARA BAIXO e depois ENTER
    input_tomselect = driver.find_element(By.CSS_SELECTOR, ".ts-control input")
    input_tomselect.send_keys(Keys.ARROW_DOWN)
    input_tomselect.send_keys(Keys.ENTER)

    # PASSO 4: PREENCHER QUANTIDADES E VALORES
    driver.find_element(By.NAME, 'quantidade_disponivel').send_keys(str(random.randint(10, 50)))
    
    # Preencher o Valor Unitário: enviamos apenas números contínuos. 
    # O JavaScript mascaraMoeda do seu site vai converter "15000" para "150,00" automaticamente.
    valor_falso = str(random.randint(1000, 35000))
    driver.find_element(By.NAME, 'valor_unitario').send_keys(valor_falso)
    
    driver.find_element(By.NAME, 'nivel_minimo').send_keys(str(random.randint(2, 5)))
    driver.find_element(By.NAME, 'nivel_maximo').send_keys(str(random.randint(20, 100)))
    
    time.sleep(1) 
    
    # Clicar no botão Salvar Peça
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    
    print("Peça cadastrada com sucesso ")
    time.sleep(3)

except Exception as e:
    print(f"Ocorreu um erro: {e}")

finally:
    driver.quit()