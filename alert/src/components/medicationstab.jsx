import React from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { Bell, Calendar } from 'lucide-react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import MedicationDialog from '@/components/MedicationDialog';

function MedicationsTab({ medications, addMedication, toggleMedication }) {
  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Recordatorios</h2>
        <MedicationDialog onAdd={addMedication} />
      </div>

      <div className="space-y-3">
        <AnimatePresence>
          {medications.map((med) => (
            <motion.div
              key={med.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
            >
              <Card className={`gradient-card p-4 ${med.active ? 'border-l-4 border-pink-500' : 'opacity-60'}`}>
                <div className="flex items-center justify-between">
                  <div className="space-y-1">
                    <div className="font-semibold">{med.name}</div>
                    <div className="text-sm text-gray-600">{med.time} - {med.days}</div>
                  </div>
                  <Button variant="ghost" size="sm" onClick={() => toggleMedication(med.id)} className={med.active ? 'text-pink-600' : 'text-gray-400'}>
                    <Bell className="w-4 h-4" />
                  </Button>
                </div>
              </Card>
            </motion.div>
          ))}
        </AnimatePresence>

        {medications.length === 0 && (
          <Card className="gradient-card p-8 text-center">
            <Calendar className="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p className="text-gray-600">No hay recordatorios configurados</p>
            <p className="text-sm text-gray-500">Agrega tu primer recordatorio</p>
          </Card>
        )}
      </div>
    </div>
  );
}

export default MedicationsTab;