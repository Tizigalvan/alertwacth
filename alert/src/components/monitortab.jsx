import React from 'react';
import { motion } from 'framer-motion';
import { AlertTriangle, Heart } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useToast } from '@/components/ui/use-toast';

function MonitorTab({
  currentHeartRate,
  isConnected,
  connectToDevice,
  stats
}) {
  const { toast } = useToast();
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ delay: 0.1 }}
    >
      <Card className="gradient-card p-6 text-center space-y-4">
        <div className="flex items-center justify-center space-x-2">
          <div className={`w-3 h-3 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'} animate-pulse`}></div>
          <span className="text-sm font-medium">
            {isConnected ? 'Conectado' : 'Desconectado'}
          </span>
        </div>
        
        <div className="space-y-2">
          <div className="text-6xl font-bold text-pink-600 heart-pulse">
            {currentHeartRate}
          </div>
          <div className="text-lg text-gray-600">BPM</div>
          
          {isConnected && <div className="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
              <div className="pulse-wave h-full w-8"></div>
            </div>}
        </div>

        <Button
          onClick={connectToDevice}
          className={`w-full ${isConnected ? 'bg-red-500 hover:bg-red-600' : 'gradient-pink'} text-white`}
        >
          {isConnected ? 'Desconectar' : 'Conectar'}
        </Button>
      </Card>

      <div className="grid grid-cols-3 gap-3 mt-4">
        <Card className="gradient-card p-4 text-center">
          <div className="text-2xl font-bold text-green-600">{stats.min}</div>
          <div className="text-xs text-gray-600">Mínimo</div>
        </Card>
        <Card className="gradient-card p-4 text-center">
          <div className="text-2xl font-bold text-blue-600">{stats.avg}</div>
          <div className="text-xs text-gray-600">Promedio</div>
        </Card>
        <Card className="gradient-card p-4 text-center">
          <div className="text-2xl font-bold text-red-600">{stats.max}</div>
          <div className="text-xs text-gray-600">Máximo</div>
        </Card>
      </div>

      {(currentHeartRate > 100 || currentHeartRate < 50) && isConnected && (
        <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }} className="mt-4">
          <Card className="gradient-card p-4 border-l-4 border-red-500">
            <div className="flex items-center space-x-3">
              <AlertTriangle className="w-5 h-5 text-red-500" />
              <div>
                <div className="font-semibold text-red-700">Anomalía Detectada</div>
                <div className="text-sm text-gray-600">
                  {currentHeartRate > 100 ? 'Frecuencia cardíaca elevada' : 'Frecuencia cardíaca baja'}
                </div>
              </div>
            </div>
          </Card>
        </motion.div>
      )}
    </motion.div>
  );
}

export default MonitorTab;