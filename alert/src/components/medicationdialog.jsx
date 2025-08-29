import React, { useState } from 'react';
import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

function MedicationDialog({ onAdd }) {
  const [open, setOpen] = useState(false);
  const [medication, setMedication] = useState({ name: '', time: '', days: 'Todos los días' });

  const handleSubmit = (e) => {
    e.preventDefault();
    if (medication.name && medication.time) {
      onAdd(medication);
      setMedication({ name: '', time: '', days: 'Todos los días' });
      setOpen(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button size="sm" className="gradient-pink text-white">
          <Plus className="w-4 h-4 mr-1" />
          Agregar
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Nuevo Recordatorio</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="med-name">Nombre del Medicamento</Label>
            <Input
              id="med-name"
              placeholder="Ej: Aspirina"
              value={medication.name}
              onChange={(e) => setMedication((prev) => ({ ...prev, name: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="med-time">Hora</Label>
            <Input
              id="med-time"
              type="time"
              value={medication.time}
              onChange={(e) => setMedication((prev) => ({ ...prev, time: e.target.value }))}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="med-days">Frecuencia</Label>
            <select
              id="med-days"
              className="w-full p-2 border border-gray-300 rounded-md"
              value={medication.days}
              onChange={(e) => setMedication((prev) => ({ ...prev, days: e.target.value }))}
            >
              <option>Todos los días</option>
              <option>Lunes a Viernes</option>
              <option>Fines de semana</option>
              <option>Cada 8 horas</option>
              <option>Cada 12 horas</option>
            </select>
          </div>
          <Button type="submit" className="w-full gradient-pink text-white">
            Agregar Recordatorio
          </Button>
        </form>
      </DialogContent>
    </Dialog>
  );
}

export default MedicationDialog;