import React, { useState } from 'react';
import { Helmet } from 'react-helmet';
import { motion } from 'framer-motion';
import { Heart, Bell, BarChart3, Settings } from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Toaster } from '@/components/ui/toaster';
import { useToast } from '@/components/ui/use-toast';

import useHeartRateMonitor from '@/hooks/useHeartRateMonitor';
import useMedicationManager from '@/hooks/useMedicationManager';

import MonitorTab from '@/components/MonitorTab';
import MedicationsTab from '@/components/MedicationsTab';
import HistoryTab from '@/components/HistoryTab';
import SettingsTab from '@/components/SettingsTab';

function App() {
  const [isConnected, setIsConnected] = useState(false);
  const [emergencyContact, setEmergencyContact] = useState('');
  const [activeTab, setActiveTab] = useState('monitor');
  const { toast } = useToast();

  const { currentHeartRate, heartRateHistory, stats } = useHeartRateMonitor(isConnected, emergencyContact);
  const { medications, addMedication, toggleMedication } = useMedicationManager();

  const connectToDevice = () => {
    setIsConnected(!isConnected);
    toast({
      title: isConnected ? "Desconectado" : "Conectado",
      description: isConnected ? "Pulsera desconectada" : "Pulsera conectada exitosamente"
    });
  };

  return (
    <>
      <Helmet>
        <title>AlertWatch - Monitoreo Cardíaco Inteligente</title>
        <meta name="description" content="Aplicación móvil para monitoreo de pulsaciones cardíacas con detección de anomalías y recordatorios de medicamentos" />
        <meta property="og:title" content="AlertWatch - Monitoreo Cardíaco Inteligente" />
        <meta property="og:description" content="Aplicación móvil para monitoreo de pulsaciones cardíacas con detección de anomalías y recordatorios de medicamentos" />
      </Helmet>

      <div className="mobile-container">
        <div className="p-4 space-y-6">
          {/* Header */}
          <motion.div initial={{ opacity: 0, y: -20 }} animate={{ opacity: 1, y: 0 }} className="text-center space-y-2">
            <div className="flex items-center justify-center space-x-2">
              <Heart className="w-8 h-8 text-pink-500 heart-pulse" />
              <h1 className="text-2xl font-bold bg-gradient-to-r from-pink-600 to-pink-800 bg-clip-text text-transparent">
                AlertWatch
              </h1>
            </div>
            <p className="text-gray-600 text-sm">Tu salud cardíaca bajo control</p>
          </motion.div>

          {/* Tabs Navigation */}
          <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
            <TabsList className="grid w-full grid-cols-4 bg-white/50 backdrop-blur-sm">
              <TabsTrigger value="monitor" className="flex flex-col items-center space-y-1 data-[state=active]:bg-pink-500 data-[state=active]:text-white">
                <Heart className="w-4 h-4" />
                <span className="text-xs">Monitor</span>
              </TabsTrigger>
              <TabsTrigger value="medications" className="flex flex-col items-center space-y-1 data-[state=active]:bg-pink-500 data-[state=active]:text-white">
                <Bell className="w-4 h-4" />
                <span className="text-xs">Medicinas</span>
              </TabsTrigger>
              <TabsTrigger value="history" className="flex flex-col items-center space-y-1 data-[state=active]:bg-pink-500 data-[state=active]:text-white">
                <BarChart3 className="w-4 h-4" />
                <span className="text-xs">Historial</span>
              </TabsTrigger>
              <TabsTrigger value="settings" className="flex flex-col items-center space-y-1 data-[state=active]:bg-pink-500 data-[state=active]:text-white">
                <Settings className="w-4 h-4" />
                <span className="text-xs">Config</span>
              </TabsTrigger>
            </TabsList>

            {/* Tab Contents */}
            <TabsContent value="monitor" className="space-y-4">
              <MonitorTab
                currentHeartRate={currentHeartRate}
                isConnected={isConnected}
                connectToDevice={connectToDevice}
                stats={stats}
              />
            </TabsContent>

            <TabsContent value="medications" className="space-y-4">
              <MedicationsTab
                medications={medications}
                addMedication={addMedication}
                toggleMedication={toggleMedication}
              />
            </TabsContent>

            <TabsContent value="history" className="space-y-4">
              <HistoryTab
                heartRateHistory={heartRateHistory}
                stats={stats}
              />
            </TabsContent>

            <TabsContent value="settings" className="space-y-4">
              <SettingsTab
                emergencyContact={emergencyContact}
                setEmergencyContact={setEmergencyContact}
                isConnected={isConnected}
              />
            </TabsContent>
          </Tabs>
        </div>
        <Toaster />
      </div>
    </>
  );
}

export default App;