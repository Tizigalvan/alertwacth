import React, { useState } from 'react';
import { useToast } from '@/components/ui/use-toast';

const useMedicationManager = () => {
  const [medications, setMedications] = useState([]);
  const { toast } = useToast();

  const addMedication = (medication) => {
    const newMed = { id: Date.now(), ...medication, active: true };
    setMedications(prev => [...prev, newMed]);
    toast({
      title: "💊 Recordatorio Agregado",
      description: `${medication.name} - ${medication.time}`
    });
  };

  const toggleMedication = (id) => {
    setMedications(prev => prev.map(med => med.id === id ? { ...med, active: !med.active } : med));
  };

  return {
    medications,
    addMedication,
    toggleMedication
  };
};

export default useMedicationManager;