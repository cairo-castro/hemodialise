#!/usr/bin/env python3
import pandas as pd
import os
import json

def extract_excel_content(file_path):
    """Extrai conteúdo de arquivo Excel e retorna estrutura de dados"""
    try:
        # Ler todas as abas do arquivo Excel
        excel_file = pd.ExcelFile(file_path)
        content = {}

        for sheet_name in excel_file.sheet_names:
            # Tentar diferentes estratégias de leitura
            try:
                # Estratégia 1: Ler normalmente
                df = pd.read_excel(file_path, sheet_name=sheet_name)
            except:
                # Estratégia 2: Pular linhas vazias no início
                df = pd.read_excel(file_path, sheet_name=sheet_name, skiprows=5)

            # Encontrar a linha com os cabeçalhos reais
            header_row = None
            for idx, row in df.iterrows():
                row_values = [str(val) for val in row.values if str(val).strip() and str(val) != 'nan']
                if len(row_values) >= 3:  # Se tem pelo menos 3 campos preenchidos
                    # Verificar se parece com cabeçalhos
                    if any(keyword in str(row_values).upper() for keyword in ['DATA', 'HORA', 'PACIENTE', 'TURNO', 'EQUIPAMENTO', 'RESPONSÁVEL']):
                        header_row = idx
                        break

            if header_row is not None:
                # Re-ler com o cabeçalho correto
                df = pd.read_excel(file_path, sheet_name=sheet_name, header=header_row)

            # Limpar dados
            df = df.dropna(how='all')  # Remover linhas completamente vazias
            df = df.dropna(axis=1, how='all')  # Remover colunas completamente vazias

            content[sheet_name] = {
                'columns': df.columns.tolist(),
                'data': df.fillna('').to_dict('records')[:15],  # Primeiras 15 linhas
                'total_rows': len(df),
                'sample_data': []
            }

            # Extrair dados de exemplo mais informativos
            for idx, row in df.head(10).iterrows():
                row_data = {}
                for col in df.columns:
                    val = row[col]
                    if pd.notna(val) and str(val).strip():
                        row_data[col] = str(val)
                if row_data:  # Só adicionar se tem dados
                    content[sheet_name]['sample_data'].append(row_data)

        return content
    except Exception as e:
        return {'error': str(e)}

def main():
    files = [
        'CHCK LIST PARA SEGURANÇA DO PACIENTE EM HEMODIALISE..xlsx',
        'DESINFECÇÃO QUIMICA (1)..xlsx',
        'LIMPEZA E DESINFECÇÃO DOS EQUIPAMENTOS - ATUALIZADO.xlsx'
    ]

    for file_name in files:
        file_path = f'/home/Hemodialise/{file_name}'
        if os.path.exists(file_path):
            print(f"\n=== ANÁLISE: {file_name} ===")
            content = extract_excel_content(file_path)

            if 'error' in content:
                print(f"Erro: {content['error']}")
                continue

            for sheet_name, sheet_data in content.items():
                print(f"\nABA: {sheet_name}")
                print(f"Total de linhas: {sheet_data['total_rows']}")
                print(f"Colunas: {', '.join(sheet_data['columns'])}")

                if sheet_data['data']:
                    print("Exemplo de dados:")
                    for i, row in enumerate(sheet_data['data'][:3]):  # Mostrar apenas 3 primeiras linhas
                        print(f"  Linha {i+1}: {dict(list(row.items())[:3])}")  # Apenas 3 primeiros campos

if __name__ == "__main__":
    main()