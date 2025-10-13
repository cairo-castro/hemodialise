#!/usr/bin/env python3
"""
Script para simplificar o modal de pacientes
Remove: prontuário, alergias, observações
Mantém: nome, data, fator RH
"""

file_path = 'resources/js/mobile/views/PatientsPage.vue'

# Ler o arquivo
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Atualizar canCreatePatient
old_can_create = """const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0 &&
         newPatient.value.medical_record.length > 0;
});"""

new_can_create = """const canCreatePatient = computed(() => {
  return newPatient.value.full_name.length > 0 &&
         newPatient.value.birth_date.length > 0;
});"""

content = content.replace(old_can_create, new_can_create)

# 2. Simplificar reset do newPatient no createPatient
old_reset = """    // Reset form
    newPatient.value = {
      full_name: '',
      birth_date: '',
      medical_record: '',
      blood_type: '',
      allergies: '',
      observations: ''
    };"""

new_reset = """    // Reset form
    newPatient.value = {
      full_name: '',
      birth_date: '',
      medical_record: '', // Mantém para compatibilidade backend
      blood_type: '',
      allergies: '',
      observations: ''
    };"""

content = content.replace(old_reset, new_reset)

# Salvar
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("✅ Arquivo atualizado com sucesso!")
print("📝 Próximo passo: substituir o HTML e CSS do modal")
