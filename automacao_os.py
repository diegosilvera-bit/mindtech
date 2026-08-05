from datetime import datetime, timedelta
import random
import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select, WebDriverWait
from webdriver_manager.chrome import ChromeDriverManager

# 1. PERGUNTA NO TERMINAL (ANTES DE ABRIR O NAVEGADOR)
try:
    qtd_testes = int(input("Quantos testes gostaria de cadastrar? "))
except ValueError:
    print("Valor inválido! Executando 1 teste por padrão.")
    qtd_testes = 1

# 2. INICIALIZAÇÃO DO NAVEGADOR E FERRAMENTAS
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
        print(f"\n--- Executando Abertura de O.S. {i} de {qtd_testes} ---")

        # Acesse a tela de abertura de O.S.
        driver.get("http://localhost:8080/mindtech/ordens_servico/cadastrar.php")

        # 1. BUSCA UM CLIENTE QUE POSSUA EQUIPAMENTO CADASTRADO
        ts_cliente_control = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]")
        ))
        ts_cliente_control.click()
        time.sleep(0.5)

        # Mapeia quantas opções de clientes existem no TomSelect
        opcoes_clientes = driver.find_elements(
            By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'option')]"
        )
        total_clientes = len(opcoes_clientes)

        cliente_encontrado = False

        for idx in range(total_clientes):
            # Se não for o primeiro da lista, reabre o TomSelect de clientes
            if idx > 0:
                ts_cliente_control = driver.find_element(
                    By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]"
                )
                ts_cliente_control.click()
                time.sleep(0.5)

            # Re-busca as opções para evitar elementos obsoletos (Stale Element)
            opcoes = driver.find_elements(
                By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'option')]"
            )
            
            if idx < len(opcoes):
                opcoes[idx].click()
                time.sleep(VELOCIDADE) # Aguarda o JavaScript 'filtrarEquipamentos()' rodar

            # Abre o TomSelect de Equipamentos para verificar se o cliente tem aparelhos
            ts_equip_control = driver.find_element(
                By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'ts-control')]"
            )
            ts_equip_control.click()
            time.sleep(0.5)

            opcoes_equip = driver.find_elements(
                By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'option')]"
            )

            # Se houver pelo menos 1 equipamento, seleciona o primeiro e segue o fluxo
            if len(opcoes_equip) > 0:
                opcoes_equip[0].click()
                cliente_encontrado = True
                time.sleep(VELOCIDADE)
                break
            else:
                print(f"⚠️ Cliente {idx + 1} não tem equipamento cadastrado. Testando o próximo...")

        if not cliente_encontrado:
            print("❌ Nenhum cliente da lista possui equipamentos cadastrados!")
            print("💡 Dica: Execute o script 'automacao_equipamentos.py' para cadastrar aparelhos primeiro.")
            break

        # 2. SELECIONAR TÉCNICO (TomSelect)
        ts_tecnico = driver.find_element(
            By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//div[contains(@class, 'ts-control')]"
        )
        ts_tecnico.click()
        time.sleep(0.3)
        
        input_ts_tec = driver.find_element(By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//input")
        input_ts_tec.send_keys(Keys.ARROW_DOWN)
        input_ts_tec.send_keys(Keys.ENTER)
        time.sleep(VELOCIDADE)

        # 3. STATUS INICIAL E PREVISÃO DE ENTREGA
        select_status = Select(driver.find_element(By.NAME, 'status'))
        select_status.select_by_value("EM_ANALISE")
        time.sleep(VELOCIDADE)

        data_futura = (datetime.now() + timedelta(days=5)).strftime("%d%m%Y")
        campo_data = driver.find_element(By.NAME, 'data_prevista_entrega')
        campo_data.send_keys(data_futura)
        time.sleep(VELOCIDADE)

        # 4. DESCREVER O PROBLEMA RELATADO
        problemas_comuns = [
            "Aparelho não liga após sofrer uma queda. Cliente solicita orçamento prévio.",
            "Tela trincada com falha no touch. Aparelho liga e emite sons normalmente.",
            "Bateria descarregando muito rápido e esquentando durante o uso.",
            "Conector de carga danificado. Não reconhece o cabo do carregador.",
            "Limpeza preventiva e troca de pasta térmica. Equipamento desligando por superaquecimento."
        ]
        
        campo_obs = driver.find_element(By.NAME, 'observacoes')
        campo_obs.send_keys(random.choice(problemas_comuns))
        time.sleep(VELOCIDADE)

        # 5. GRAVAR A O.S.
        btn_salvar = driver.find_element(By.XPATH, "//button[@type='submit']")
        btn_salvar.click()
        
        print(f"✅ Ordem de Serviço {i} criada com sucesso!")
        time.sleep(VELOCIDADE)

    print("\n🎉 Processo de Abertura de O.S. finalizado!")

except Exception as e:
    print(f"❌ Ocorreu um erro durante a execução: {e}")

finally:
    driver.quit()