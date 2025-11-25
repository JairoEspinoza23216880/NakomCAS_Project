const API_URL = "http://localhost:8000/api";

export async function registerUser(userData) {
  try {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(userData),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error en el registro');
    }
    
    return data;
  } catch (error) {
    throw error;
  }
}

// Función para login
export async function loginUser(credentials) {
  try {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(credentials),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error en el login');
    }
    
    return data;
  } catch (error) {
    throw error;
  }
}

// Función para verificar sesión
export async function validateSession(token) {
  try {
    const response = await fetch(`${API_URL}/me`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error en la validación de sesión');
    }
    
    return data;
  } catch (error) {
    throw error;
  }
}

// Nueva función para buscar configuración
export async function searchConfiguration(configData) {
  try {
    const response = await fetch(`${API_URL}/configurator/search`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(configData),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error en la búsqueda de configuración');
    }
    
    return data;
  } catch (error) {
    throw error;
  }
}

// Nueva función para crear pedido
export async function createOrder(orderData, token) {
  try {
    const response = await fetch(`${API_URL}/orders`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(orderData),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Error al crear el pedido');
    }
    
    return data;
  } catch (error) {
    throw error;
  }
}