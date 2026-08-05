"""
TESTE AUTOMATIZADO - GERAÇÃO DE RELATÓRIOS (VERSÃO DASHBOARD)
Sistema: Mindtech
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from datetime import datetime, timedelta
import random
import time
import os
import webbrowser

class TesteAutomatizadoRelatorio:
    def __init__(self, url_base="http://localhost:8080/mindtech"):
        self.url_base = url_base
        self.diretorio_teste = "TesteGeracaoRelatorio"
        
        # Cria a pasta se não existir
        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)
            
        # Lista para armazenar resultados do relatório
        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")
        
        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)
        
        print("✓ Ambiente preparado e pasta 'TesteGeracaoRelatorio' verificada!")

    def realizar_login(self, usuario="admin", senha="admin"):
        print("\n🔐 Realizando login no sistema...")
        self.driver.get(f"{self.url_base}/login.php")
        
        campo_login = self.wait.until(EC.visibility_of_element_located((By.NAME, 'login')))
        campo_login.send_keys(usuario) 
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
            <title>Dashboard de Testes - Mindtech Relatórios</title>
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
                <h1>Relatório de Automação - Geração de Relatórios</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Relatório / Período</th>
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

        opcoes_relatorio = ["faturamento", "ordens_servico", "pecas_baixo_estoque"]

        for i in range(1, quantidade + 1):
            print(f"\n🚀 Executando Geração de Relatório {i} de {quantidade}...")
            status = "Falha"
            detalhe_teste = "Não identificado"

            try:
                self.driver.get(f"{self.url_base}/relatorios/cadastrar.php")

                # 1. Selecionar o Tipo de Relatório
                select_tipo_elem = self.wait.until(EC.visibility_of_element_located((By.NAME, 'tipo_relatorio')))
                select_tipo = Select(select_tipo_elem)
                opcao_escolhida = random.choice(opcoes_relatorio)
                select_tipo.select_by_value(opcao_escolhida)
                time.sleep(0.5)

                # 2. Definir o Intervalo de Datas
                hoje = datetime.now()
                inicio = hoje - timedelta(days=30)
                
                data_inicio_str = inicio.strftime("%d%m%Y")
                data_fim_str = hoje.strftime("%d%m%Y")

                campo_inicio = self.driver.find_element(By.NAME, 'data_inicio')
                campo_inicio.send_keys(data_inicio_str)
                time.sleep(0.5)

                campo_fim = self.driver.find_element(By.NAME, 'data_fim')
                campo_fim.send_keys(data_fim_str)
                time.sleep(0.5)

                detalhe_teste = f"Tipo: {opcao_escolhida} ({inicio.strftime('%d/%m/%Y')} a {hoje.strftime('%d/%m/%Y')})"

                # 3. Gerar Relatório
                btn_gerar = self.driver.find_element(By.XPATH, "//button[@type='submit']")
                btn_gerar.click()
                time.sleep(1)

                status = "Sucesso"
                print(f"✅ Relatório '{opcao_escolhida}' gerado com sucesso!")

            except Exception as e:
                print(f"✗ Erro na geração do relatório: {e}")

            # Captura de screenshot e adição ao relatório
            nome_print = self.tirar_screenshot(f"relatorio_{i}.png")
            self.resultados_testes.append({
                "id": i,
                "detalhes": detalhe_teste,
                "status": status,
                "screenshot": nome_print
            })

        # Finalização e geração do relatório
        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()
        
        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open('file://' + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- SISTEMA DE AUTOMAÇÃO MINDTECH - GERAÇÃO DE RELATÓRIOS ---")
    try:
        qtd = int(input("Quantos relatórios você deseja gerar hoje? "))
        if qtd > 0:
            URL_LOCAL = "http://localhost:8080/mindtech"
            teste = TesteAutomatizadoRelatorio(url_base=URL_LOCAL)
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")