from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
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
    # PASSO 1: LOGIN NO SISTEMA (Apenas 1 vez no início)
    driver.get("http://localhost:8080/mindtech/login.php")
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin')
    time.sleep(VELOCIDADE)
    
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Cadastro de Usuário {i} de {qtd_testes} ---")

        # Acesse a tela de cadastro de usuários
        driver.get("http://localhost:8080/mindtech/usuarios/cadastrar.php")

        # 1. Preencher Nome Completo
        campo_nome = wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
        campo_nome.send_keys(fake.name())
        time.sleep(VELOCIDADE)

        # 2. Selecionar Perfil de Acesso ('A' - Atendimento, 'T' - Técnico, 'E' - Estoquista, 'G' - Gerente)
        select_perfil_elem = driver.find_element(By.NAME, 'perfil')
        select_perfil = Select(select_perfil_elem)
        perfil_escolhido = random.choice(['A', 'T', 'E', 'G'])
        select_perfil.select_by_value(perfil_escolhido)
        time.sleep(VELOCIDADE)

        # 3. Preencher E-mail
        driver.find_element(By.NAME, 'email').send_keys(fake.email())
        time.sleep(VELOCIDADE)

        # 4. Preencher Login
        driver.find_element(By.NAME, 'login').send_keys(fake.user_name())
        time.sleep(VELOCIDADE)

        # 5. Preencher Senha
        driver.find_element(By.NAME, 'senha').send_keys('Mudar@123')
        time.sleep(VELOCIDADE)

        # 6. Salvar Usuário
        btn_salvar = driver.find_element(By.XPATH, "//button[@type='submit']")
        btn_salvar.click()
        
        print(f"✅ Usuário {i} criado com sucesso (Perfil: {perfil_escolhido})!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os cadastros de usuários foram concluídos com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()