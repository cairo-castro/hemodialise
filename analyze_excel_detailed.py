#!/usr/bin/env python3
import pandas as pd

def analyze_detailed(file_path, sheet_name, start_row=None):
    print(f"\n{'='*100}")
    print(f"File: {file_path}")
    print(f"Sheet: {sheet_name}")
    print(f"{'='*100}\n")

    # Read without headers to see raw structure
    df = pd.read_excel(file_path, sheet_name=sheet_name, header=None)

    print(f"Total rows: {len(df)}")
    print(f"Total columns: {len(df.columns)}\n")

    # Print all rows to understand structure
    for idx, row in df.iterrows():
        print(f"Row {idx:2d}: ", end="")
        non_null_values = []
        for col_idx, val in enumerate(row):
            if pd.notna(val) and str(val).strip() != '':
                non_null_values.append(f"Col{col_idx}='{val}'")
        if non_null_values:
            print(" | ".join(non_null_values[:10]))  # Show first 10 non-null values
        else:
            print("[empty row]")

# Analyze both files
print("\n" + "="*100)
print("ANALYZING CHEMICAL DISINFECTION FILE")
print("="*100)
analyze_detailed(
    "/home/Hemodialise/sistema-hemodialise/DESINFECÇÃO QUIMICA (1)..xlsx",
    "CONTROLE DE LIMPEZA"
)

print("\n\n" + "="*100)
print("ANALYZING CLEANING AND SURFACE DISINFECTION FILE")
print("="*100)
analyze_detailed(
    "/home/Hemodialise/sistema-hemodialise/LIMPEZA E DESINFECÇÃO DOS EQUIPAMENTOS - ATUALIZADO.xlsx",
    "CONTROLE DE LIMPEZA"
)
