import React, { useState } from 'react';
import { Phone } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/use-toast';

function SettingsTab({ emergencyContact, setEmergencyContact, isConnected }) {
  const { toast } = useToast();
  const [minThreshold, setMinThreshold] = useState(50);
  const [maxThreshold, setMaxThreshold] = useState(100);

  const handleSaveSettings = () => {
    // In a real app, you'd save these to localStorage or a backend
    toast({
      title: "✅ Configuración Guardada",
      description: "Tus preferencias han sido actualizadas"
    });
  };

  return (
    <div className="space-y-4">
      <h2 className="text-lg font-semibold">Configuración</h2>
      
      <Card className="gradient-card p-4 space-y-4">
        <div className="space-y-2">
          <Label htmlFor="emergency">Contacto de Emergencia</Label>
          <div className="flex space-x-2">
            <Input
              id="emergency"
              placeholder="+1234567890"
              value={emergencyContact}
              onChange={(e) => setEmergencyContact(e.target.value)}
              className="flex-1"
            />
            <Button size="icon" variant="outline" onClick={() => toast({
              title: "🚧 Esta función no está implementada aún—¡pero no te preocupes! ¡Puedes solicitarla en tu próximo prompt! 🚀"
            })}>
              <Phone className="w-4 h-4" />
            </Button>
          </div>
        </div>

        <div className="space-y-3">
          <h3 className="font-semibold">Umbrales de Alerta</h3>
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-2">
              <Label>Mínimo (BPM)</Label>
              <Input
                defaultValue={minThreshold}
                type="number"
                onChange={(e) => setMinThreshold(Number(e.target.value))}
              />
            </div>
            <div className="space-y-2">
              <Label>Máximo (BPM)</Label>
              <Input
                defaultValue={maxThreshold}
                type="number"
                onChange={(e) => setMaxThreshold(Number(e.target.value))}
              />
            </div>
          </div>
        </div>

        <Button className="w-full gradient-pink text-white" onClick={handleSaveSettings}>
          Guardar Configuración
        </Button>
      </Card>

      <Card className="gradient-card p-4">
        <h3 className="font-semibold mb-3">Información de la App</h3>
        <div className="space-y-2 text-sm text-gray-600">
          <div className="flex justify-between">
            <span>Versión:</span>
            <span>1.5.2</span>
          </div>
          <div className="flex justify-between">
            <span>Dispositivo:</span>
            <span>{isConnected ? 'ESP32 Conectado' : 'No Conectado'}</span>
          </div>
          <div className="flex justify-between">
            <span>Estado:</span>
            <span className={isConnected ? 'text-green-600' : 'text-red-600'}>
              {isConnected ? 'Activo' : 'Inactivo'}
            </span>
          </div>
        </div>
      </Card>
    </div>
  );
}

export default SettingsTab;