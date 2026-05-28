CREATE DATABASE locadora;

CREATE TABLE filmes (
    id SERIAL PRIMARY KEY,
    titulo TEXT NOT NULL,
    genero TEXT NOT NULL,
    quantidade_total INT NOT NULL DEFAULT 1,
    quantidade_disponivel INT NOT NULL DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE locacoes (
    id SERIAL PRIMARY KEY,
    filme_id INT NOT NULL,
    nome_cliente TEXT NOT NULL,
    data_locacao DATE NOT NULL DEFAULT CURRENT_DATE,
    data_devolucao DATE NOT NULL,
    status_pagamento TEXT NOT NULL DEFAULT 'Pendente',
    devolvido BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_filme FOREIGN KEY (filme_id) REFERENCES filmes(id)
);