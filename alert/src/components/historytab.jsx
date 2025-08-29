import React from 'react';
import { BarChart3 } from 'lucide-react';
import { Card } from '@/components/ui/card';

function HistoryTab({ heartRateHistory, stats }) {
  return (
    <div className="space-y-4">
      <h2 className="text-lg font-semibold">Historial de Hoy</h2>
      
      <div className="grid grid-cols-3 gap-3 mb-4">
        <Card className="gradient-card p-4 text-center">
          <div className="text-xl font-bold text-green-600">{stats.min}</div>
          <div className="text-xs text-gray-600">Mínimo Hoy</div>
        </Card>
        <Card className="gradient-card p-4 text-center">
          <div className="text-xl font-bold text-blue-600">{stats.avg}</div>
          <div className="text-xs text-gray-600">Promedio</div>
        </Card>
        <Card className="gradient-card p-4 text-center">
          <div className="text-xl font-bold text-red-600">{stats.max}</div>
          <div className="text-xs text-gray-600">Máximo Hoy</div>
        </Card>
      </div>

      <Card className="gradient-card p-4">
        <h3 className="font-semibold mb-3">Lecturas Recientes</h3>
        <div className="space-y-2 max-h-64 overflow-y-auto">
          {heartRateHistory.slice(-10).reverse().map((reading, index) => (
            <div key={index} className="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
              <span className="text-sm text-gray-600">{reading.time}</span>
              <span className={`font-semibold ${reading.rate > 100 ? 'text-red-600' : reading.rate < 50 ? 'text-orange-600' : 'text-green-600'}`}>
                {reading.rate} BPM
              </span>
            </div>
          ))}
          
          {heartRateHistory.length === 0 && (
            <div className="text-center py-8 text-gray-500">
              <BarChart3 className="w-12 h-12 mx-auto mb-3 text-gray-400" />
              <p>No hay datos disponibles</p>
              <p className="text-sm">Conecta tu dispositivo para comenzar</p>
            </div>
          )}
        </div>
      </Card>
    </div>
  );
}

export default HistoryTab;