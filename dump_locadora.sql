--
-- PostgreSQL database dump
--

\restrict gX6CnE8POhLdDxcUrCgCL8IDyAQauIswkk5H6DyP2VAjYxVgDPmUPXjPZylXhn1

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: filmes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.filmes (
    id integer NOT NULL,
    titulo text NOT NULL,
    genero text NOT NULL,
    quantidade_total integer DEFAULT 1 NOT NULL,
    quantidade_disponivel integer DEFAULT 1 NOT NULL,
    ativo boolean DEFAULT true,
    foto character varying
);


ALTER TABLE public.filmes OWNER TO postgres;

--
-- Name: filmes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.filmes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.filmes_id_seq OWNER TO postgres;

--
-- Name: filmes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.filmes_id_seq OWNED BY public.filmes.id;


--
-- Name: locacoes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.locacoes (
    id integer NOT NULL,
    filme_id integer NOT NULL,
    nome_cliente text NOT NULL,
    data_locacao date DEFAULT CURRENT_DATE NOT NULL,
    data_devolucao date NOT NULL,
    status_pagamento text DEFAULT 'Pendente'::text NOT NULL,
    devolvido boolean DEFAULT false
);


ALTER TABLE public.locacoes OWNER TO postgres;

--
-- Name: locacoes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.locacoes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.locacoes_id_seq OWNER TO postgres;

--
-- Name: locacoes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.locacoes_id_seq OWNED BY public.locacoes.id;


--
-- Name: filmes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.filmes ALTER COLUMN id SET DEFAULT nextval('public.filmes_id_seq'::regclass);


--
-- Name: locacoes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.locacoes ALTER COLUMN id SET DEFAULT nextval('public.locacoes_id_seq'::regclass);


--
-- Data for Name: filmes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.filmes (id, titulo, genero, quantidade_total, quantidade_disponivel, ativo, foto) FROM stdin;
\.


--
-- Data for Name: locacoes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.locacoes (id, filme_id, nome_cliente, data_locacao, data_devolucao, status_pagamento, devolvido) FROM stdin;
\.


--
-- Name: filmes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.filmes_id_seq', 5, true);


--
-- Name: locacoes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.locacoes_id_seq', 3, true);


--
-- Name: filmes filmes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.filmes
    ADD CONSTRAINT filmes_pkey PRIMARY KEY (id);


--
-- Name: locacoes locacoes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.locacoes
    ADD CONSTRAINT locacoes_pkey PRIMARY KEY (id);


--
-- Name: locacoes fk_filme; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.locacoes
    ADD CONSTRAINT fk_filme FOREIGN KEY (filme_id) REFERENCES public.filmes(id);


--
-- PostgreSQL database dump complete
--

\unrestrict gX6CnE8POhLdDxcUrCgCL8IDyAQauIswkk5H6DyP2VAjYxVgDPmUPXjPZylXhn1

