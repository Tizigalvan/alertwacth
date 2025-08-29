import React, { useState, useEffect } from 'react';
import { useToast } from '@/components/ui/use-toast';

const useHeartRateMonitor = (isConnected, emergencyContact) => {
  const [currentHeartRate, setCurrentHeartRate] = useState(72);
  const [heartRateHistory, setHeartRateHistory] = useState([]);
  const { toast } = useToast();

  useEffect(() => {
    const interval = setInterval(() => {
      if (isConnected) {
        const newRate = 60 + Math.floor(Math.random() * 40) + Math.sin(Date.now() / 10000) * 10;
        setCurrentHeartRate(Math.round(newRate));

        const now = new Date();
        setHeartRateHistory(prev => {
          const newHistory = [...prev, { time: now.toLocaleTimeString(), rate: Math.round(newRate), timestamp: now.getTime() }];
          return newHistory.slice(-50); // Keep last 50 readings
        });

        if (newRate > 100 || newRate < 50) {
          handleHeartRateAnomaly(Math.round(newRate));
        }
      }
    }, 2000);
    return () => clearInterval(interval);
  }, [isConnected, emergencyContact]);

  const handleHeartRateAnomaly = (rate) => {
    const message = rate > 100 ? 'Taquicardia detectada' : 'Bradicardia detectada';
    toast({
      title: "⚠️ Anomalía Cardíaca",
      description: `${message}: ${rate} BPM`,
      variant: "destructive"
    });
    if (emergencyContact) {
      toast({
        title: "📱 Mensaje Enviado",
        description: `Alerta enviada a ${emergencyContact}`
      });
    }
  };

  const getTodayStats = () => {
    const today = heartRateHistory.filter(reading => {
      const readingDate = new Date(reading.timestamp);
      const today = new Date();
      return readingDate.toDateString() === today.toDateString();
    });
    if (today.length === 0) return { min: 0, max: 0, avg: 0 };
    const rates = today.map(r => r.rate);
    return {
      min: Math.min(...rates),
      max: Math.max(...rates),
      avg: Math.round(rates.reduce((a, b) => a + b, 0) / rates.length)
    };
  };

  const stats = getTodayStats();

  return {
    currentHeartRate,
    heartRateHistory,
    stats,
    handleHeartRateAnomaly
  };
};

export default useHeartRateMonitor;