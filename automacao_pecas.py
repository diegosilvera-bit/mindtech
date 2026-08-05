from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time
import random

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
    
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Cadastro de Peça {i} de {qtd_testes} ---")

        # Acesse a tela de cadastro de peças
        URL_PECAS = "http://localhost:8080/mindtech/estoque/cadastrar.php" 
        driver.get(URL_PECAS)

        # 1. Preencher Código
        campo_codigo = wait.until(EC.visibility_of_element_located((By.NAME, 'codigo')))
        codigo_falso = fake.bothify(text='PC-####-??', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ')
        campo_codigo.send_keys(codigo_falso)
        time.sleep(VELOCIDADE)
        
        # 2. Preencher Descrição
        descricoes = [
            "Tela Display LCD iPhone 13 Original",
            "Bateria Samsung Galaxy S21",
            "Teclado Notebook Dell Inspiron 15",
            "Placa Mãe Asus Prime A320M",
            "Memória RAM Corsair 8GB DDR4"
        ]
        driver.find_element(By.NAME, 'descricao').send_keys(random.choice(descricoes))
        time.sleep(VELOCIDADE)

        # 3. Lidar com o TomSelect (Fornecedor)
        caixa_tomselect = driver.find_element(By.CSS_SELECTOR, ".ts-control")
        caixa_tomselect.click()
        time.sleep(0.5)
        
        input_tomselect = driver.find_element(By.CSS_SELECTOR, ".ts-control input")
        input_tomselect.send_keys(Keys.ARROW_DOWN)
        input_tomselect.send_keys(Keys.ENTER)
        time.sleep(VELOCIDADE)

        # 4. Preencher Quantidades e Valores
        driver.find_element(By.NAME, 'quantidade_disponivel').send_keys(str(random.randint(10, 50)))
        time.sleep(VELOCIDADE)
        
        valor_falso = str(random.randint(1000, 35000))
        driver.find_element(By.NAME, 'valor_unitario').send_keys(valor_falso)
        time.sleep(VELOCIDADE)
        
        driver.find_element(By.NAME, 'nivel_minimo').send_keys(str(random.randint(2, 5)))
        time.sleep(VELOCIDADE)

        driver.find_element(By.NAME, 'nivel_maximo').send_keys(str(random.randint(20, 100)))
        time.sleep(VELOCIDADE)
        
        # 5. Clicar no botão Salvar Peça
        driver.find_element(By.XPATH, "//button[@type='submit']").click()
        
        print(f"✅ Peça {i} cadastrada com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os cadastros de peças foram concluídos com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()