-- Schema para PostgreSQL
-- Execute este arquivo no seu banco PostgreSQL

-- Criar banco de dados (execute separadamente se necessário)
-- CREATE DATABASE salgados_da_sara;

-- Conectar ao banco salgados_da_sara antes de executar o resto

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    endereco TEXT NOT NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(255),
    cidade VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    eh_admin BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de administradores
CREATE TABLE IF NOT EXISTS usuarios_admin (
    id SERIAL PRIMARY KEY,
    nome_usuario VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    funcao VARCHAR(50) DEFAULT 'admin',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de produtos
CREATE TABLE IF NOT EXISTS produtos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT,
    eh_porcionado BOOLEAN DEFAULT FALSE,
    eh_personalizado BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    numero_pedido VARCHAR(50) UNIQUE NOT NULL,
    usuario_id INTEGER REFERENCES usuarios(id),
    dados_cliente JSONB NOT NULL,
    itens JSONB NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    taxa_entrega DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    eh_entrega BOOLEAN DEFAULT FALSE,
    metodo_pagamento VARCHAR(50) DEFAULT 'dinheiro',
    status VARCHAR(50) DEFAULT 'pendente',
    motivo_rejeicao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de histórico de status dos pedidos
CREATE TABLE IF NOT EXISTS historico_status_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER REFERENCES pedidos(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS configuracoes_app (
    id SERIAL PRIMARY KEY,
    chave_config VARCHAR(100) UNIQUE NOT NULL,
    valor_config TEXT NOT NULL,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir administrador padrão
INSERT INTO usuarios_admin (nome_usuario, senha, funcao) 
VALUES ('sara', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON CONFLICT (nome_usuario) DO NOTHING;
-- Senha padrão: password

-- Inserir produtos padrão
INSERT INTO produtos (nome, preco, categoria, descricao, eh_porcionado, eh_personalizado) VALUES
('Coxinha de Frango', 110.00, 'salgados', 'Coxinha tradicional com recheio de frango desfiado', false, false),
('Coxinha de Frango com Catupiry', 120.00, 'salgados', 'Coxinha de frango com cremoso catupiry', false, false),
('Bolinha de Queijo', 100.00, 'salgados', 'Bolinha crocante recheada com queijo', false, false),
('Risole de Camarão', 130.00, 'salgados', 'Risole recheado com camarão temperado', false, false),
('Pastel de Carne', 90.00, 'salgados', 'Pastel frito com recheio de carne moída', false, false),
('Pastel de Queijo', 85.00, 'salgados', 'Pastel frito com recheio de queijo', false, false),
('Enroladinho de Salsicha', 95.00, 'salgados', 'Massa crocante envolvendo salsicha', false, false),
('Sortido Simples', 95.00, 'sortidos', 'Mix de salgados variados', false, false),
('Sortido Especial', 110.00, 'sortidos', 'Mix premium de salgados especiais', false, false),
('Pão de Açúcar', 100.00, 'assados', 'Pão doce tradicional assado', false, false),
('Pão de Batata', 105.00, 'assados', 'Pão macio com batata', false, false),
('Esfirra de Carne', 120.00, 'assados', 'Esfirra assada com recheio de carne', false, false),
('Esfirra de Queijo', 115.00, 'assados', 'Esfirra assada com recheio de queijo', false, false),
('Torta Salgada', 25.00, 'especiais', 'Fatia de torta salgada', true, false),
('Quiche', 20.00, 'especiais', 'Fatia de quiche', true, false),
('Refrigerante Lata', 5.00, 'opcionais', 'Refrigerante em lata 350ml', true, false),
('Suco Natural', 8.00, 'opcionais', 'Suco natural 300ml', true, false)
ON CONFLICT DO NOTHING;

-- Inserir configurações padrão
INSERT INTO configuracoes_app (chave_config, valor_config) VALUES
('taxa_entrega', '10.00'),
('valor_minimo_pedido', '50.00'),
('endereco_loja', 'RUA IDA BERLET 1738 B'),
('telefone_loja', '(54) 99999-9999')
ON CONFLICT (chave_config) DO NOTHING;

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario_id ON pedidos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_status ON pedidos(status);
CREATE INDEX IF NOT EXISTS idx_pedidos_criado_em ON pedidos(criado_em);
CREATE INDEX IF NOT EXISTS idx_historico_status_pedido_id ON historico_status_pedido(pedido_id);
CREATE INDEX IF NOT EXISTS idx_produtos_categoria ON produtos(categoria);