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
    # PASSO 1: FAZER O LOGIN NO SISTEMA (Apenas 1 vez no início)
    URL_LOGIN = "http://localhost:8080/mindtech/login.php" 
    driver.get(URL_LOGIN)
    
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    driver.find_element(By.XPATH, "//button[@type='submit']").click()
    time.sleep(VELOCIDADE)

    # PASSO 2: LOOP DE REPETIÇÃO DOS TESTES
    for i in range(1, qtd_testes + 1):
        print(f"\n--- Executando Cadastro de Equipamento {i} de {qtd_testes} ---")

        # Abre a lista de clientes
        URL_LISTA = "http://localhost:8080/mindtech/clientes/listar.php" 
        driver.get(URL_LISTA)

        # 1. Clicar no botão da tabela para ver equipamentos do cliente
        btn_abrir_equipamentos = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[@title='Equipamentos do Cliente']")))
        btn_abrir_equipamentos.click()
        time.sleep(VELOCIDADE)

        # 2. Clicar em "Novo Equipamento" (Abrir 2º Modal)
        btn_novo_equipamento = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(@onclick, 'abrirModalCadastrarEquip')]")))
        btn_novo_equipamento.click()
        time.sleep(VELOCIDADE)

        # 3. Selecionar 'Tipo' no modal
        xpath_tipo = "//div[@id='modalCadastrarEquip']//select[@name='tipo']"
        campo_tipo = wait.until(EC.visibility_of_element_located((By.XPATH, xpath_tipo)))
        tipos_disponiveis = ["Notebook", "Desktop (PC)", "Smartphone", "Tablet", "Monitor", "Impressora"]
        Select(campo_tipo).select_by_visible_text(random.choice(tipos_disponiveis))
        time.sleep(VELOCIDADE)

        # 4. Preencher 'Marca'
        marcas = ["Dell", "HP", "Lenovo", "Samsung", "Apple", "Asus", "Motorola"]
        campo_marca = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='marca']")
        campo_marca.send_keys(random.choice(marcas))
        time.sleep(VELOCIDADE)

        # 5. Preencher 'Modelo'
        modelo_falso = f"Pro {fake.bothify(text='??-####')}"
        campo_modelo = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='modelo']")
        campo_modelo.send_keys(modelo_falso)
        time.sleep(VELOCIDADE)

        # 6. Preencher 'Número de Série'
        serie_falsa = fake.bothify(text='SN-########-??', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ')
        campo_serie = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='numero_serie']")
        campo_serie.send_keys(serie_falsa)
        time.sleep(VELOCIDADE)

        # 7. Preencher 'Observações'
        obs = fake.text(max_nb_chars=80)
        campo_obs = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//textarea[@name='observacoes']")
        campo_obs.send_keys(obs)
        time.sleep(VELOCIDADE)

        # 8. Clicar no botão Salvar
        btn_salvar = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//button[@type='submit']")
        btn_salvar.click()
        print(f"✅ Equipamento {i} cadastrado com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Todos os cadastros de equipamentos foram finalizados com sucesso!")

except Exception as e:
    print(f"❌ Ocorreu um erro: {e}")

finally:
    driver.quit()