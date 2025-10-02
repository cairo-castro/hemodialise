#!/usr/bin/env python3
import pandas as pd
import sys
import json

def analyze_excel(file_path):
    print(f"\n{'='*80}")
    print(f"Analyzing: {file_path}")
    print(f"{'='*80}\n")

    try:
        # Read all sheets
        excel_file = pd.ExcelFile(file_path)
        print(f"Sheets found: {excel_file.sheet_names}\n")

        for sheet_name in excel_file.sheet_names:
            print(f"\n{'-'*80}")
            print(f"Sheet: {sheet_name}")
            print(f"{'-'*80}")

            df = pd.read_excel(file_path, sheet_name=sheet_name)

            print(f"\nShape: {df.shape[0]} rows x {df.shape[1]} columns")
            print(f"\nColumns: {list(df.columns)}")

            print(f"\nFirst 20 rows:")
            print(df.head(20).to_string())

            print(f"\n\nData types:")
            print(df.dtypes)

            # Check for merged cells or special patterns
            print(f"\n\nSample of non-null values per column:")
            for col in df.columns:
                non_null = df[col].dropna()
                if len(non_null) > 0:
                    print(f"  {col}: {non_null.iloc[0]} (total non-null: {len(non_null)})")

    except Exception as e:
        print(f"Error reading {file_path}: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    files = [
        "/home/Hemodialise/sistema-hemodialise/DESINFECÇÃO QUIMICA (1)..xlsx",
        "/home/Hemodialise/sistema-hemodialise/LIMPEZA E DESINFECÇÃO DOS EQUIPAMENTOS - ATUALIZADO.xlsx"
    ]

    for file in files:
        analyze_excel(file)
