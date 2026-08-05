"""
TESTE AUTOMATIZADO - ABERTURA DE ORDEM DE SERVIÇO (VERSÃO DASHBOARD)
Sistema: Mindtech
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from datetime import datetime, timedelta
import random
import time
import os
import webbrowser

class TesteAutomatizadoOrdemServico:
    def __init__(self, url_base="http://localhost:8080/mindtech"):
        self.url_base = url_base
        self.diretorio_teste = "TesteOrdemServico"
        
        # Cria a pasta se não existir
        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)
            
        # Lista para armazenar resultados do relatório
        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")
        
        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)
        
        print("✓ Ambiente preparado e pasta 'TesteOrdemServico' verificada!")

    def realizar_login(self, usuario="admin", senha="admin"):
        print("\n🔐 Realizando login no sistema...")
        self.driver.get(f"{self.url_base}/login.php")
        
        self.wait.until(EC.visibility_of_element_located((By.NAME, 'login'))).send_keys(usuario)
        self.driver.find_element(By.NAME, 'senha').send_keys(senha)
        self.driver.find_element(By.XPATH, "//button[@type='submit']").click()

        self.wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))
        time.sleep(1)

    def tirar_screenshot(self, nome_arquivo):
        caminho = os.path.join(self.diretorio_teste, nome_arquivo)
        self.driver.save_screenshot(caminho)
        return nome_arquivo

    def gerar_relatorio_html(self):
        caminho_html = os.path.join(self.diretorio_teste, "dashboard.html")
        
        # Contagem para o resumo
        sucessos = sum(1 for r in self.resultados_testes if r['status'] == 'Sucesso')
        falhas = len(self.resultados_testes) - sucessos

        html_content = f"""
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Dashboard de Testes - Mindtech O.S.</title>
            <style>
                body {{ font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 20px; }}
                .container {{ max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }}
                h1 {{ color: #004a80; text-align: center; }}
                .summary {{ display: flex; justify-content: space-around; margin-bottom: 30px; padding: 15px; background: #e9ecef; border-radius: 5px; }}
                .card {{ text-align: center; }}
                .card h2 {{ margin: 0; font-size: 2em; }}
                .status-sucesso {{ color: #28a745; }}
                .status-falha {{ color: #dc3545; }}
                table {{ width: 100%; border-collapse: collapse; margin-top: 20px; }}
                th, td {{ padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }}
                th {{ background-color: #004a80; color: white; }}
                .img-link {{ color: #007bff; text-decoration: none; font-weight: bold; }}
                tr:hover {{ background-color: #f1f1f1; }}
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Relatório de Automação de Abertura de O.S.</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente / Detalhes</th>
                            <th>Status</th>
                            <th>Evidência</th>
                        </tr>
                    </thead>
                    <tbody>
        """
        
        for r in self.resultados_testes:
            cor_status = "status-sucesso" if r['status'] == 'Sucesso' else "status-falha"
            html_content += f"""
                <tr>
                    <td>{r['id']}</td>
                    <td>{r['detalhes']}</td>
                    <td class="{cor_status}">{r['status']}</td>
                    <td><a class="img-link" href="{r['screenshot']}" target="_blank">Visualizar Screenshot</a></td>
                </tr>
            """

        html_content += """
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        """

        with open(caminho_html, "w", encoding="utf-8") as f:
            f.write(html_content)
        
        return caminho_html

    def executar_teste_completo(self, quantidade):
        # Passo prévio: realizar login no sistema
        try:
            self.realizar_login()
        except Exception as e:
            print(f"❌ Falha crítica ao tentar realizar login: {e}")
            self.driver.quit()
            return

        for i in range(1, quantidade + 1):
            print(f"\n🚀 Executando Abertura de O.S. {i} de {quantidade}...")
            status = "Falha"
            detalhe_teste = "Não identificado"

            try:
                self.driver.get(f"{self.url_base}/ordens_servico/cadastrar.php")
                self.wait.until(EC.presence_of_element_located((By.ID, "id_cliente")))
                time.sleep(1)

                # --- A. SELECIONAR CLIENTE (CLIQUE REAL NO TOMSELECT) ---
                print("🔍 Abrindo lista de clientes...")
                ts_cliente = self.wait.until(EC.element_to_be_clickable(
                    (By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]")
                ))
                ts_cliente.click()
                time.sleep(0.5)

                opcoes_clientes = self.driver.find_elements(
                    By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'option') and not(contains(@class, 'disabled'))]"
                )

                if not opcoes_clientes:
                    print("❌ Nenhuma opção de cliente foi encontrada no sistema.")
                    detalhe_teste = "Sem clientes disponíveis"
                    raise Exception("Sem clientes disponíveis")

                total_clientes = len(opcoes_clientes)
                cliente_selecionado = False

                for index in range(total_clientes):
                    if index > 0:
                        ts_cliente = self.driver.find_element(
                            By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'ts-control')]"
                        )
                        ts_cliente.click()
                        time.sleep(0.5)

                    opcoes_atuais = self.driver.find_elements(
                        By.XPATH, "//select[@id='id_cliente']/following-sibling::div//div[contains(@class, 'option') and not(contains(@class, 'disabled'))]"
                    )

                    if index >= len(opcoes_atuais):
                        break

                    cliente_elem = opcoes_atuais[index]
                    nome_cliente = cliente_elem.text.strip()
                    print(f"🔄 Testando cliente ({index + 1}/{total_clientes}): {nome_cliente}")
                    cliente_elem.click()

                    time.sleep(1.5)

                    # --- B. VERIFICAR EQUIPAMENTOS ---
                    ts_equip = self.driver.find_element(
                        By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'ts-control')]"
                    )
                    ts_equip.click()
                    time.sleep(0.5)

                    opcoes_equip = self.driver.find_elements(
                        By.XPATH, "//select[@id='id_equipamento']/following-sibling::div//div[contains(@class, 'option') and not(contains(@class, 'disabled'))]"
                    )

                    if len(opcoes_equip) > 0:
                        equip_elem = random.choice(opcoes_equip)
                        nome_equip = equip_elem.text.strip()
                        equip_elem.click()
                        print(f"🎯 Equipamento selecionado: {nome_equip}")
                        cliente_selecionado = True
                        detalhe_teste = f"Cliente: {nome_cliente} | Equip: {nome_equip}"
                        time.sleep(0.5)
                        break
                    else:
                        print(f"⚠️ O cliente '{nome_cliente}' não possui equipamentos. Tentando o próximo...")

                if not cliente_selecionado:
                    detalhe_teste = "Nenhum cliente com equipamento"
                    raise Exception("Nenhum cliente com equipamento cadastrado localizado")

                # --- C. SELECIONAR TÉCNICO ---
                print("👨‍🔧 Selecionando técnico...")
                ts_tec = self.driver.find_element(
                    By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//div[contains(@class, 'ts-control')]"
                )
                ts_tec.click()
                time.sleep(0.5)

                opcoes_tec = self.driver.find_elements(
                    By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//div[contains(@class, 'option') and not(contains(@class, 'disabled'))]"
                )
                if opcoes_tec:
                    random.choice(opcoes_tec).click()
                else:
                    input_tec = self.driver.find_element(By.XPATH, "//select[@id='id_tecnico']/following-sibling::div//input")
                    input_tec.send_keys(Keys.ARROW_DOWN)
                    input_tec.send_keys(Keys.ENTER)
                time.sleep(0.5)

                # --- D. STATUS E PREVISÃO DE ENTREGA ---
                print("📅 Preenchendo status e data...")
                self.driver.execute_script("""
                    var select = document.querySelector("select[name='status']");
                    if(select) { select.value = 'EM_ANALISE'; }
                """)

                dias = random.randint(3, 10)
                data_futura = (datetime.now() + timedelta(days=dias)).strftime("%d%m%Y")
                
                campo_data = self.driver.find_element(By.NAME, 'data_prevista_entrega')
                campo_data.clear()
                campo_data.send_keys(data_futura)

                # --- E. OBSERVAÇÕES ---
                print("📝 Escrevendo observações...")
                problemas = [
                    "Aparelho não liga após sofrer uma queda. Cliente solicita orçamento prévio.",
                    "Tela trincada com falha no touch. Aparelho liga e emite sons normalmente.",
                    "Bateria descarregando muito rápido e esquentando durante o uso.",
                    "Conector de carga danificado. Não reconhece o cabo do carregador."
                ]
                campo_obs = self.driver.find_element(By.NAME, 'observacoes')
                campo_obs.clear()
                campo_obs.send_keys(random.choice(problemas))

                time.sleep(1)

                # --- F. SALVAR O.S. ---
                print("💾 Salvando Ordem de Serviço...")
                btn_salvar = self.driver.find_element(By.XPATH, "//button[@type='submit']")
                btn_salvar.click()
                time.sleep(1.5)

                status = "Sucesso"
                print(f"✅ Ordem de Serviço {i} cadastrada com sucesso!")

            except Exception as e:
                print(f"✗ Erro no processo: {e}")

            # Captura de screenshot e registro dos resultados
            nome_print = self.tirar_screenshot(f"ordem_servico_{i}.png")
            self.resultados_testes.append({
                "id": i,
                "detalhes": detalhe_teste,
                "status": status,
                "screenshot": nome_print
            })

        # Finalização e relatório
        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()
        
        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open('file://' + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- SISTEMA DE AUTOMAÇÃO MINDTECH - ABERTURA DE O.S. ---")
    try:
        qtd = int(input("Quantas Ordens de Serviço você deseja cadastrar hoje? "))
        if qtd > 0:
            URL_LOCAL = "http://localhost:8080/mindtech"
            teste = TesteAutomatizadoOrdemServico(url_base=URL_LOCAL)
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")