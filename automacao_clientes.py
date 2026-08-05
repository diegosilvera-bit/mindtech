"""
TESTE AUTOMATIZADO - CADASTRO DE CLIENTE (VERSÃO DASHBOARD)
Sistema: Mindtech
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from faker import Faker
import time
import os
import webbrowser

class TesteAutomatizadoCliente:
    def __init__(self, url_base="http://localhost:8080/mindtech"):
        self.url_base = url_base
        self.diretorio_teste = "TesteCadastroCliente"
        self.fake = Faker('pt_BR')
        
        # Cria a pasta se não existir
        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)
            
        # Lista para armazenar resultados do relatório
        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")
        
        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)
        
        print("✓ Ambiente preparado e pasta 'TesteCadastroCliente' verificada!")

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
            <title>Dashboard de Testes - Mindtech Clientes</title>
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
                <h1>Relatório de Automação de Cadastro de Clientes</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente Cadastrado</th>
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
                    <td>{r['nome']}</td>
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
            print(f"\n🚀 Executando Cadastro de Cliente {i} de {quantidade}...")
            status = "Falha"
            
            # Geração dos dados aleatórios via Faker
            nome_cliente = self.fake.name()
            data_nasc = self.fake.date_of_birth(minimum_age=18, maximum_age=80).strftime('%d/%m/%Y')
            cpf_cliente = self.fake.cpf()
            rg_cliente = self.fake.rg()
            telefone_cliente = self.fake.cellphone_number()
            endereco_cliente = self.fake.street_address()

            try:
                self.driver.get(f"{self.url_base}/clientes/cadastrar.php")

                # 1. Preencher Nome
                campo_nome = self.wait.until(EC.visibility_of_element_located((By.NAME, 'nome')))
                campo_nome.send_keys(nome_cliente)
                time.sleep(0.5)

                # 2. Preencher Data de Nascimento
                self.driver.find_element(By.NAME, 'data_nascimento').send_keys(data_nasc)
                time.sleep(0.5)

                # 3. Preencher CPF e RG
                self.driver.find_element(By.NAME, 'cpf').send_keys(cpf_cliente)
                time.sleep(0.5)

                self.driver.find_element(By.NAME, 'rg').send_keys(rg_cliente)
                time.sleep(0.5)

                # 4. Preencher Telefone
                self.driver.find_element(By.NAME, 'telefone').send_keys(telefone_cliente)
                time.sleep(0.5)

                # 5. Preencher Endereço
                self.driver.find_element(By.NAME, 'endereco').send_keys(endereco_cliente)
                time.sleep(0.5)

                # 6. Salvar
                self.driver.find_element(By.XPATH, "//button[@type='submit']").click()
                time.sleep(1)

                status = "Sucesso"
                print(f"✅ Cliente '{nome_cliente}' cadastrado com sucesso!")

            except Exception as e:
                print(f"✗ Erro no cadastro do cliente: {e}")

            # Captura de screenshot e adição ao relatório
            nome_print = self.tirar_screenshot(f"cliente_{i}.png")
            self.resultados_testes.append({
                "id": i,
                "nome": nome_cliente,
                "status": status,
                "screenshot": nome_print
            })

        # Finalização e geração do relatório
        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()
        
        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open('file://' + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- SISTEMA DE AUTOMAÇÃO MINDTECH - CADASTRO DE CLIENTE ---")
    try:
        qtd = int(input("Quantos clientes você deseja cadastrar hoje? "))
        if qtd > 0:
            URL_LOCAL = "http://localhost:8080/mindtech"
            teste = TesteAutomatizadoCliente(url_base=URL_LOCAL)
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")