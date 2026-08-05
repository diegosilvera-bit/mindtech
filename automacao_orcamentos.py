"""
TESTE AUTOMATIZADO - CADASTRO DE ORÇAMENTO (VERSÃO DASHBOARD)
Sistema: MindTech
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.common.exceptions import (
    ElementClickInterceptedException,
    StaleElementReferenceException,
    TimeoutException
)
import time
import random
import os
import webbrowser


class TesteAutomatizadoOrcamento:
    def __init__(self, url_base="http://localhost:8080/mindtech", login="admin", senha="admin"):
        self.url_base = url_base
        self.login_usuario = login
        self.login_senha = senha
        self.diretorio_teste = "TesteOrcamentos"

        # Cria a pasta se não existir
        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)

        # Lista para armazenar resultados do relatório
        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(
            service=Service(ChromeDriverManager().install()),
            options=chrome_options
        )
        self.wait = WebDriverWait(self.driver, 20)

        print("✓ Ambiente preparado e pasta 'TesteOrcamentos' verificada!")

        self.fazer_login()

    # ------------------------------------------------------------------
    # AUXILIARES
    # ------------------------------------------------------------------

    def fazer_login(self):
        self.driver.get(f"{self.url_base}/login.php")
        self.preencher_campo(By.NAME, "login", self.login_usuario)
        self.preencher_campo(By.NAME, "senha", self.login_senha)
        self.clicar_seguro(By.XPATH, "//button[@type='submit']")
        time.sleep(1)

    def clicar_seguro(self, by, valor, timeout=20):
        """Espera o elemento ficar clicável, leva até ele e tenta clicar.
        Se houver uma sobreposição momentânea, tenta novamente e usa
        JavaScript como último recurso.
        """
        ultimo_erro = None

        for tentativa in range(3):
            try:
                elemento = WebDriverWait(self.driver, timeout).until(
                    EC.element_to_be_clickable((by, valor))
                )

                self.driver.execute_script(
                    "arguments[0].scrollIntoView({block: 'center', inline: 'center'});",
                    elemento
                )
                time.sleep(0.5)

                try:
                    elemento.click()
                except ElementClickInterceptedException as erro:
                    ultimo_erro = erro
                    self.driver.execute_script("arguments[0].click();", elemento)

                return

            except (StaleElementReferenceException, ElementClickInterceptedException) as erro:
                ultimo_erro = erro
                time.sleep(1)

        raise ultimo_erro

    def preencher_campo(self, by, valor, texto):
        campo = WebDriverWait(self.driver, 20).until(
            EC.visibility_of_element_located((by, valor))
        )
        campo.clear()
        campo.send_keys(texto)
        return campo

    def confirmar_sweetalert_se_existir(self, timeout=3):
        """Se o clique em 'Gravar' abrir um SweetAlert2 pedindo confirmação,
        clica em 'Confirmar'. Se não aparecer nenhum popup, segue sem erro.
        """
        try:
            botao_confirmar = WebDriverWait(self.driver, timeout).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, ".swal2-confirm"))
            )
            self.driver.execute_script("arguments[0].click();", botao_confirmar)
            return True
        except TimeoutException:
            return False

    def aguardar_gravacao(self, url_atual, timeout=15):
        """Confirma que o orçamento foi realmente salvo, esperando por UM
        dos sinais abaixo: a URL mudar (redirect) ou aparecer um SweetAlert
        de sucesso. Retorna False se nenhum sinal aparecer.
        """
        try:
            WebDriverWait(self.driver, timeout).until(
                lambda d: d.current_url != url_atual
                or len(d.find_elements(By.CSS_SELECTOR, ".swal2-icon-success")) > 0
            )
            return True
        except TimeoutException:
            return False

    def tirar_screenshot(self, nome_arquivo):
        caminho = os.path.join(self.diretorio_teste, nome_arquivo)
        self.driver.save_screenshot(caminho)
        return nome_arquivo

    # ------------------------------------------------------------------
    # RELATÓRIO
    # ------------------------------------------------------------------

    def gerar_relatorio_html(self):
        caminho_html = os.path.join(self.diretorio_teste, "dashboard.html")

        sucessos = sum(1 for r in self.resultados_testes if r['status'] == 'Sucesso')
        falhas = len(self.resultados_testes) - sucessos

        html_content = f"""
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Dashboard de Testes - MindTech</title>
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
                <h1>Relatório de Automação de Orçamentos</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>O.S. Vinculada</th>
                            <th>Valor Mão de Obra</th>
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
                    <td>{r['os']}</td>
                    <td>{r['valor_mao_obra']}</td>
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

    # ------------------------------------------------------------------
    # EXECUÇÃO
    # ------------------------------------------------------------------

    def executar_teste_completo(self, quantidade):
        for i in range(quantidade):
            print(f"\n🚀 Iniciando cadastro de orçamento {i + 1} de {quantidade}...")
            status = "Falha"
            os_selecionada = "-"
            valor_mao_obra_usado = "-"

            try:
                self.driver.get(f"{self.url_base}/orcamentos/cadastrar.php")

                # 1. Selecionar a próxima O.S. em aberto
                select_os_elem = self.wait.until(
                    EC.visibility_of_element_located((By.NAME, "id_os"))
                )
                select_os = Select(select_os_elem)

                if len(select_os.options) <= 1:
                    print("⚠️ Nenhuma Ordem de Serviço em aberto disponível para orçamento.")
                    self.resultados_testes.append({
                        "id": i + 1,
                        "os": "Sem O.S. disponível",
                        "valor_mao_obra": "-",
                        "status": "Falha",
                        "screenshot": self.tirar_screenshot(f"orcamento_{i + 1}.png")
                    })
                    break

                select_os.select_by_index(1)
                os_selecionada = select_os.first_selected_option.text
                time.sleep(1)

                # 2. Adicionar Peça Dinamicamente
                select_peca_elem = self.wait.until(
                    EC.visibility_of_element_located((By.ID, "select_peca"))
                )
                select_peca = Select(select_peca_elem)

                if len(select_peca.options) > 1:
                    select_peca.select_by_index(1)
                    time.sleep(1)

                    self.preencher_campo(By.ID, "qtd_peca", "2")
                    time.sleep(1)

                    self.clicar_seguro(
                        By.XPATH,
                        "//button[contains(normalize-space(.), '+ Add')]"
                    )
                    time.sleep(1)

                # 3. Informar Mão de Obra
                campo_mao_obra = self.wait.until(
                    EC.visibility_of_element_located((By.ID, "valor_mao_obra"))
                )
                campo_mao_obra.clear()

                valor_mao_obra_usado = str(random.choice([8000, 12000, 15000, 20000]))
                campo_mao_obra.send_keys(valor_mao_obra_usado)
                time.sleep(1)

                # 4. Gravar o Orçamento
                url_antes_de_gravar = self.driver.current_url
                self.clicar_seguro(By.XPATH, "//button[@type='submit']")

                self.confirmar_sweetalert_se_existir()

                if self.aguardar_gravacao(url_antes_de_gravar):
                    status = "Sucesso"
                    print(f"✅ Orçamento {i + 1} salvo com sucesso!")
                else:
                    print(f"⚠️ Orçamento {i + 1}: não foi possível confirmar a gravação no banco.")

            except Exception as e:
                print(f"✗ Erro no processo: {e}")

            nome_print = self.tirar_screenshot(f"orcamento_{i + 1}.png")
            self.resultados_testes.append({
                "id": i + 1,
                "os": os_selecionada,
                "valor_mao_obra": valor_mao_obra_usado,
                "status": status,
                "screenshot": nome_print
            })

            time.sleep(1)

        # Finalização
        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()

        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open('file://' + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- SISTEMA DE AUTOMAÇÃO MINDTECH ---")
    try:
        qtd = int(input("Quantos orçamentos você deseja cadastrar hoje? "))
        if qtd > 0:
            URL_LOCAL = "http://localhost:8080/mindtech"
            teste = TesteAutomatizadoOrcamento(url_base=URL_LOCAL)
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")