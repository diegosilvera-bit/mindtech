from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from faker import Faker
import time
import random

fake = Faker('pt_BR')
driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()))
wait = WebDriverWait(driver, 10)

# Tempo de pausa entre as etapas para acompanhamento visual.
VELOCIDADE = 1.0 

try:
    # PASSO 1: LOGIN NO SISTEMA (como Gerente)
    driver.get("http://localhost/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: ACESSAR A TELA DE NOVO USUÁRIO
    driver.get("http://localhost:8080/mindtech/usuarios/cadastrar.php")

    # PASSO 3: PREENCHER NOME COMPLETO E PERFIL
    campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
    campo_nome.send_keys(fake.name())
    time.sleep(VELOCIDADE)

    # Seleciona o Perfil de Acesso ('A' - Atendimento, 'T' - Técnico, 'E' - Estoquista, 'G' - Gerente)
    select_perfil_elem = driver.find_element(By.NAME, 'perfil')
    select_perfil = Select(select_perfil_elem)
    perfil_escolhido = random.choice(['A', 'T', 'E', 'G'])
    select_perfil.select_by_value(perfil_escolhido)
    time.sleep(VELOCIDADE)

    # PASSO 4: PREENCHER E-MAIL E CREDENCIAIS
    driver.find_element(By.NAME, 'email').send_keys(fake.email())
    time.sleep(VELOCIDADE)

    driver.find_element(By.NAME, 'login').send_keys(fake.user_name())
    time.sleep(VELOCIDADE)

    driver.find_element(By.NAME, 'senha').send_keys('Mudar@123')
    time.sleep(VELOCIDADE)

    # O campo de Foto é opcional. Caso queira testar upload via Selenium no futuro,
    # basta usar: driver.find_element(By.NAME, 'foto').send_keys('/caminho/para/imagem.jpg')

    # PASSO 5: SALVAR USUÁRIO
    btn_salvar = driver.find_element(By.XPATH, "//button[@type='submit']")
    btn_salvar.click()
    
    print(f"Usuário criado com sucesso (Perfil: {perfil_escolhido})!")
    time.sleep(2)

except Exception as e:
    print(f"Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()