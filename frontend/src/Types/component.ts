export type ComponentType = "CPU" | "GPU" | "RAM" | "Motherboard" | "Storage" | "PSU" | "Case";

export interface Componente {
    id: string; 
    nombre: string;
    tipo: ComponentType;
    gama: number; 
    precio: number;
    stock: number;
    estado: "Activo" | "Inactivo";
}