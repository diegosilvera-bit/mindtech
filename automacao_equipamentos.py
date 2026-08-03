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

# Configurando a "Espera Inteligente": o robô vai esperar até 10 segundos 
# para um elemento aparecer na tela antes de dar erro.
wait = WebDriverWait(driver, 10) 

try:
    # ==========================================
    # PASSO 1: FAZER O LOGIN NO SISTEMA
    # ==========================================
    URL_LOGIN = "http://localhost/mindtech/login.php" 
    driver.get(URL_LOGIN)
    
    # Usando o wait para garantir que a página carregou
    wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys('admin')
    driver.find_element(By.NAME, 'senha').send_keys('admin') 
    driver.find_element(By.XPATH, "//button[@type='submit']").click()

    # ==========================================
    # PASSO 2: ABRIR A LISTA DE CLIENTES E O 1º MODAL
    # ==========================================
    URL_LISTA = "http://localhost/mindtech/clientes/listar.php" 
    driver.get(URL_LISTA)

    # Clicar no botão da tabela. Buscamos pelo atributo 'title' que está no seu PHP
    btn_abrir_equipamentos = wait.until(EC.element_to_be_clickable((By.XPATH, "//a[@title='Equipamentos do Cliente']")))
    btn_abrir_equipamentos.click()

    # ==========================================
    # PASSO 3: CLICAR EM "NOVO EQUIPAMENTO" (ABRIR 2º MODAL)
    # ==========================================
    # Espera a primeira janela abrir e o botão verde aparecer
    btn_novo_equipamento = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(@onclick, 'abrirModalCadastrarEquip')]")))
    btn_novo_equipamento.click()

    # ==========================================
    # PASSO 4: PREENCHER O FORMULÁRIO
    # ==========================================
    # Agora buscamos os campos ESPECIFICAMENTE dentro da div '#modalCadastrarEquip'
    
    # Aguarda a animação do segundo modal terminar e o campo 'tipo' ficar visível
    xpath_tipo = "//div[@id='modalCadastrarEquip']//select[@name='tipo']"
    campo_tipo = wait.until(EC.visibility_of_element_located((By.XPATH, xpath_tipo)))
    
    tipos_disponiveis = ["Notebook", "Desktop (PC)", "Smartphone", "Tablet", "Monitor", "Impressora"]
    Select(campo_tipo).select_by_visible_text(random.choice(tipos_disponiveis))
    
    # Preencher Marca e Modelo
    marcas = ["Dell", "HP", "Lenovo", "Samsung", "Apple", "Asus", "Motorola"]
    driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='marca']").send_keys(random.choice(marcas))
    
    modelo_falso = f"Pro {fake.bothify(text='??-####')}"
    driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='modelo']").send_keys(modelo_falso)
    
    # Preencher Série e Observações
    serie_falsa = fake.bothify(text='SN-########-??', letters='ABCDEFGHIJKLMNOPQRSTUVWXYZ')
    driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//input[@name='numero_serie']").send_keys(serie_falsa)
    
    obs = fake.text(max_nb_chars=80)
    driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//textarea[@name='observacoes']").send_keys(obs)
    
    time.sleep(1) # Pausa rápida apenas para você visualizar os dados preenchidos
    
    # Clicar no botão Salvar (dentro do modal correto)
    btn_salvar = driver.find_element(By.XPATH, "//div[@id='modalCadastrarEquip']//button[@type='submit']")
    btn_salvar.click()
    
    print("Equipamento cadastrado com sucesso seguindo o fluxo de dois modais!")
    
    # Aguardar 3 segundos para ver a mensagem verde de sucesso na tela
    time.sleep(3)

except Exception as e:
    print(f"Ocorreu um erro: {e}")

finally:
    driver.quit()