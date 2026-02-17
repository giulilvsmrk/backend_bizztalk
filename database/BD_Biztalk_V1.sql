DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;
SET timezone = 'America/La_Paz';
CREATE EXTENSION IF NOT EXISTS "unaccent";

CREATE TABLE rol (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre              TEXT NOT NULL UNIQUE,
    descripcion         TEXT,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_actualizacion TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE usuario (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombres             TEXT NOT NULL,
    apellidos           TEXT NOT NULL,
    correo              TEXT UNIQUE,
    password_hash       TEXT NOT NULL,
    telefono            TEXT NOT NULL UNIQUE,
    ubicacion_actual    POINT,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_actualizacion TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE usuario_rol (
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    id_rol              UUID NOT NULL REFERENCES rol(id) ON DELETE RESTRICT,
    fecha_asignacion    TIMESTAMPTZ DEFAULT now(),
    PRIMARY KEY (id_usuario, id_rol)
);

CREATE TABLE direccion_usuario (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    etiqueta            TEXT NOT NULL,
    ubicacion_gps       POINT NOT NULL,
    direccion_texto     TEXT NOT NULL,
    referencia          TEXT,
    es_predeterminada   BOOLEAN DEFAULT false,
    ultima_fecha_uso    TIMESTAMPTZ DEFAULT now(),
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE negocio (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_propietario      UUID NOT NULL REFERENCES usuario(id),
    nombre              TEXT NOT NULL,
    nit                 TEXT UNIQUE,
    descripcion         TEXT,
    logotipo_url        TEXT,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE sucursal (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_negocio          UUID NOT NULL REFERENCES negocio(id) ON DELETE CASCADE,
    nombre_sucursal     TEXT NOT NULL,
    ubicacion_gps       POINT NOT NULL,
    direccion_texto     TEXT NOT NULL,
    qr_estatico_url     TEXT,
    imagen_portada_url  TEXT,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE zona_cobertura (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id) ON DELETE CASCADE,
    nombre              TEXT NOT NULL,
    area                POLYGON NOT NULL,
    costo_envio         NUMERIC(12, 2) NOT NULL DEFAULT 0 CHECK (costo_envio >= 0),
    tiempo_min_extra    INT DEFAULT 0,
    activo              BOOLEAN DEFAULT true,

    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE contacto_telefonico (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    numero              TEXT NOT NULL,
    tipo                TEXT NOT NULL DEFAULT 'movil' CHECK (tipo IN ('whatsapp', 'fijo', 'movil', 'fax', 'call_center')),
    etiqueta            TEXT,
    es_principal        BOOLEAN DEFAULT false,
    id_negocio          UUID REFERENCES negocio(id) ON DELETE CASCADE,
    id_sucursal         UUID REFERENCES sucursal(id) ON DELETE CASCADE,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT chk_pertenencia CHECK (
        (id_negocio IS NOT NULL AND id_sucursal IS NULL) OR
        (id_negocio IS NULL AND id_sucursal IS NOT NULL)
    )
);

CREATE TABLE horario (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id) ON DELETE CASCADE,
    dia_semana          INT NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),
    hora_apertura       TIME NOT NULL,
    hora_cierre         TIME NOT NULL,
    es_feriado          BOOLEAN DEFAULT false,
    CONSTRAINT chk_horas CHECK (hora_cierre > hora_apertura),
    UNIQUE (id_sucursal, dia_semana)
);

CREATE TABLE config_entrega (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id) ON DELETE CASCADE,
    tipo                TEXT NOT NULL CHECK (tipo IN ('delivery', 'recojo', 'mesa')),
    alias_personalizado TEXT,
    costo_base          NUMERIC(12, 2) NOT NULL DEFAULT 0 CHECK (costo_base >= 0),
    tiempo_min_aprox    INT,
    vehiculos_permitidos JSONB DEFAULT '[]',
    activo              BOOLEAN DEFAULT true,
    UNIQUE (id_sucursal, tipo, alias_personalizado)
);

CREATE TABLE colaborador (
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id),
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    rol                 TEXT NOT NULL DEFAULT 'empleado' CHECK (rol IN ('empleado', 'encargado')),
    fecha_vinculo       TIMESTAMPTZ DEFAULT now(),
    activo              BOOLEAN DEFAULT true,
    PRIMARY KEY (id_sucursal, id_usuario)
);

CREATE TABLE galeria_sucursal (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id) ON DELETE CASCADE,
    url                 TEXT NOT NULL,
    descripcion         TEXT,
    orden_visual        INT DEFAULT 0,
    fecha_subida        TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE categoria (
    id                  INT GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
    id_negocio          UUID NOT NULL REFERENCES negocio(id),
    nombre              TEXT NOT NULL,
    UNIQUE (id_negocio, nombre)
);

CREATE TABLE producto (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_negocio          UUID NOT NULL REFERENCES negocio(id),
    id_categoria        INT REFERENCES categoria(id),
    nombre              TEXT NOT NULL,
    descripcion         TEXT,
    imagen_url          TEXT,
    precio_base         NUMERIC(12, 2) NOT NULL CHECK (precio_base >= 0),
    vector_busqueda     TSVECTOR GENERATED ALWAYS AS (
                            setweight(to_tsvector('spanish', unaccent(nombre)), 'A') ||
                            setweight(to_tsvector('spanish', unaccent(coalesce(descripcion, ''))), 'B')
                        ) STORED,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE galeria_producto (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    url                 TEXT NOT NULL,
    orden_visual        INT DEFAULT 0,
    fecha_subida        TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE inventario (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    cantidad            INT NOT NULL DEFAULT 0 CHECK (cantidad >= 0),
    precio_local        NUMERIC(12, 2) CHECK (precio_local >= 0),
    activo              BOOLEAN DEFAULT true,
    ultima_actualizacion TIMESTAMPTZ DEFAULT now(),
    UNIQUE (id_producto)
);

CREATE TABLE promocion (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    nombre              TEXT NOT NULL,
    codigo_cupon        TEXT UNIQUE,
    descripcion         TEXT,
    tipo_beneficio      TEXT NOT NULL CHECK (tipo_beneficio IN ('porcentaje', 'monto_fijo', 'envio_gratis')),
    valor_descuento     NUMERIC(12, 2) NOT NULL DEFAULT 0 CHECK (valor_descuento >= 0),
    alcance             TEXT NOT NULL CHECK (alcance IN ('producto', 'sucursal', 'global')),
    reglas_extra        JSONB DEFAULT '{}',
    id_sucursal         UUID REFERENCES sucursal(id) ON DELETE CASCADE,
    id_producto         UUID REFERENCES producto(id) ON DELETE CASCADE,
    fecha_inicio        TIMESTAMPTZ NOT NULL,
    fecha_fin           TIMESTAMPTZ NOT NULL,
    monto_minimo_compra NUMERIC(12, 2) DEFAULT 0 CHECK (monto_minimo_compra >= 0),
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_eliminacion   TIMESTAMPTZ,
    CONSTRAINT chk_alcance_valido CHECK (
        (alcance = 'global' AND id_sucursal IS NULL AND id_producto IS NULL) OR
        (alcance = 'sucursal' AND id_sucursal IS NOT NULL AND id_producto IS NULL) OR
        (alcance = 'producto' AND id_producto IS NOT NULL)
    ),
    CONSTRAINT chk_fechas_promo CHECK (fecha_fin > fecha_inicio)
);

CREATE TABLE carrito (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    id_sucursal_activa  UUID REFERENCES sucursal(id),
    fecha_actualizacion TIMESTAMPTZ DEFAULT now(),
    CONSTRAINT uq_carrito_usuario UNIQUE (id_usuario)
);

CREATE TABLE item_carrito (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_carrito          UUID NOT NULL REFERENCES carrito(id) ON DELETE CASCADE,
    id_producto         UUID NOT NULL REFERENCES producto(id),
    cantidad            INT NOT NULL CHECK (cantidad > 0),
    observacion         TEXT,
    fecha_agregado      TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE pedido (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    numero_orden_publico BIGINT GENERATED ALWAYS AS IDENTITY,
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id),
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    importe_subtotal    NUMERIC(12, 2) NOT NULL DEFAULT 0,
    importe_envio       NUMERIC(12, 2) NOT NULL DEFAULT 0,
    importe_descuento   NUMERIC(12, 2) NOT NULL DEFAULT 0,
    importe_total       NUMERIC(12, 2) NOT NULL DEFAULT 0,
    estado              TEXT DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'pagado', 'preparando', 'en_camino', 'entregado', 'cancelado')),
    canal               TEXT NOT NULL CHECK (canal IN ('app', 'ia_voz', 'ia_texto', 'web')),
    tipo_entrega        TEXT NOT NULL CHECK (tipo_entrega IN ('delivery', 'recojo', 'mesa')),
    ubicacion_entrega   POINT,
    direccion_texto     TEXT,
    fecha_programada    TIMESTAMPTZ,
    fecha_creacion      TIMESTAMPTZ DEFAULT now(),
    fecha_actualizacion TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE detalle_pedido (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pedido           UUID NOT NULL REFERENCES pedido(id),
    id_producto         UUID NOT NULL REFERENCES producto(id),
    cantidad            INT NOT NULL CHECK (cantidad > 0),
    precio_unitario     NUMERIC(12, 2) NOT NULL CHECK (precio_unitario >= 0),
    precio_original     NUMERIC(12, 2),
    descuento_aplicado  NUMERIC(12, 2) DEFAULT 0,
    subtotal            NUMERIC(12, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED
);

CREATE TABLE historial_pedido (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pedido           UUID NOT NULL REFERENCES pedido(id),
    estado_anterior     TEXT,
    estado_nuevo        TEXT NOT NULL,
    fecha_cambio        TIMESTAMPTZ DEFAULT now(),
    observacion         TEXT,
    id_usuario_operador UUID REFERENCES usuario(id)
);

CREATE TABLE uso_promocion (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_promocion        UUID NOT NULL REFERENCES promocion(id),
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    id_pedido           UUID REFERENCES pedido(id),
    monto_ahorrado      NUMERIC(12, 2) NOT NULL,
    fecha_uso           TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE billetera_usuario (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    tipo                TEXT NOT NULL CHECK (tipo IN ('efectivo', 'tarjeta_credito', 'tarjeta_debito', 'qr_bancario', 'billetera_movil')),
    proveedor           TEXT NOT NULL CHECK (proveedor IN ('stripe', 'cybersource', 'libelula', 'pasarela_qr_local', 'efectivo_manual', 'simulado', 'transferencia_manual')),
    token_externo       TEXT NOT NULL,
    marca_tarjeta       TEXT,
    ultimos_4_digitos   CHAR(4),
    fecha_expiracion    CHAR(5),
    es_predeterminado   BOOLEAN DEFAULT false,
    fecha_registro      TIMESTAMPTZ DEFAULT now(),
    activo              BOOLEAN DEFAULT true,
    fecha_eliminacion   TIMESTAMPTZ
);

CREATE TABLE transaccion_pago (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_pedido           UUID NOT NULL REFERENCES pedido(id),
    tipo_metodo         TEXT NOT NULL,
    id_metodo_guardado  UUID REFERENCES billetera_usuario(id),
    monto_total         NUMERIC(12, 2) NOT NULL,
    moneda              CHAR(3) DEFAULT 'BOB',
    estado              TEXT DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'aprobado', 'rechazado', 'reembolsado', 'expirado')),
    fecha_intento       TIMESTAMPTZ DEFAULT now(),
    fecha_confirmacion  TIMESTAMPTZ,
    id_referencia_banco TEXT,
    qr_imagen_url       TEXT,
    metadata_banco      JSONB
);

CREATE TABLE resena_producto (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    id_producto         UUID NOT NULL REFERENCES producto(id),
    id_detalle_pedido   BIGINT NOT NULL REFERENCES detalle_pedido(id),
    puntuacion          INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario          TEXT,
    fecha_registro      TIMESTAMPTZ DEFAULT now(),
    UNIQUE(id_usuario, id_detalle_pedido)
);

CREATE TABLE resena_sucursal (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id),
    id_sucursal         UUID NOT NULL REFERENCES sucursal(id),
    id_pedido           UUID NOT NULL REFERENCES pedido(id),
    puntuacion          INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    aspectos_positivos  TEXT[],
    comentario          TEXT,
    fecha_registro      TIMESTAMPTZ DEFAULT now(),
    UNIQUE(id_usuario, id_pedido)
);

CREATE TABLE lista_deseos (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    fecha_agregado      TIMESTAMPTZ DEFAULT now(),
    UNIQUE (id_usuario, id_producto)
);

CREATE TABLE item_guardado (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    id_sucursal_origen  UUID NOT NULL REFERENCES sucursal(id),
    cantidad            INT NOT NULL CHECK (cantidad > 0),
    observacion         TEXT,
    fecha_guardado      TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE coleccion (
    id                  UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    titulo              TEXT NOT NULL,
    descripcion         TEXT,
    imagen_cover_url    TEXT,
    id_usuario_destino  UUID REFERENCES usuario(id),
    es_generada_por_ia  BOOLEAN DEFAULT false,
    fecha_expiracion    TIMESTAMPTZ,
    activo              BOOLEAN DEFAULT true,
    fecha_creacion      TIMESTAMPTZ DEFAULT now()
);

CREATE TABLE item_coleccion (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_coleccion        UUID NOT NULL REFERENCES coleccion(id) ON DELETE CASCADE,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    orden_visual        INT DEFAULT 0,
    score_relevancia    NUMERIC(5, 4)
);

CREATE TABLE log_interaccion_ia (
    id                  UUID DEFAULT gen_random_uuid(),
    id_usuario          UUID REFERENCES usuario(id),
    id_pedido           UUID REFERENCES pedido(id),
    fecha_hora          TIMESTAMPTZ DEFAULT now() NOT NULL,
    transcripcion_user  TEXT,
    respuesta_ia        TEXT,
    intencion           TEXT,
    metadata_tecnica    JSONB,
    PRIMARY KEY (fecha_hora, id)
) PARTITION BY RANGE (fecha_hora);

CREATE TABLE log_ia_2025 PARTITION OF log_interaccion_ia
    FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');

CREATE TABLE historial_vista (
    id                  BIGINT GENERATED ALWAYS AS IDENTITY,
    id_usuario          UUID NOT NULL REFERENCES usuario(id) ON DELETE CASCADE,
    id_producto         UUID NOT NULL REFERENCES producto(id) ON DELETE CASCADE,
    ubicacion_viewer    POINT,
    fecha_vista         TIMESTAMPTZ DEFAULT now() NOT NULL,
    PRIMARY KEY (fecha_vista, id)
) PARTITION BY RANGE (fecha_vista);

CREATE TABLE historial_vista_2025 PARTITION OF historial_vista
    FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');

CREATE INDEX idx_sucursal_geo ON sucursal USING GIST (ubicacion_gps);
CREATE INDEX idx_zona_cobertura_area ON zona_cobertura USING GIST (area);
CREATE INDEX idx_producto_vector ON producto USING GIN (vector_busqueda);
CREATE INDEX idx_usuario_telefono ON usuario(telefono);
CREATE INDEX idx_direccion_uso ON direccion_usuario(id_usuario, ultima_fecha_uso DESC);
CREATE INDEX idx_colaborador_usuario ON colaborador(id_usuario);
CREATE INDEX idx_billetera_usuario ON billetera_usuario(id_usuario) WHERE activo = true;
CREATE INDEX idx_pedido_numero_publico ON pedido(numero_orden_publico);
CREATE INDEX idx_pedido_sucursal_estado ON pedido (id_sucursal, fecha_creacion) WHERE estado = 'pendiente';
CREATE INDEX idx_transaccion_pedido ON transaccion_pago(id_pedido);
CREATE INDEX idx_horario_sucursal ON horario(id_sucursal);
CREATE INDEX idx_config_entrega_sucursal ON config_entrega(id_sucursal) WHERE activo = true;
CREATE INDEX idx_zona_cobertura_sucursal ON zona_cobertura(id_sucursal);
CREATE INDEX idx_item_guardado_usuario ON item_guardado(id_usuario);
CREATE INDEX idx_lista_deseos_usuario ON lista_deseos(id_usuario);
CREATE INDEX idx_historial_usuario_fecha ON historial_vista(id_usuario, fecha_vista DESC);
CREATE INDEX idx_coleccion_usuario ON coleccion(id_usuario_destino) WHERE activo = true;
CREATE INDEX idx_galeria_sucursal ON galeria_sucursal(id_sucursal);
CREATE INDEX idx_galeria_producto ON galeria_producto(id_producto);
CREATE INDEX idx_resena_producto_usuario ON resena_producto(id_usuario);
CREATE INDEX idx_promocion_activa ON promocion(activo, fecha_inicio, fecha_fin);
CREATE INDEX idx_contacto_entidad ON contacto_telefonico(id_sucursal, id_negocio);

INSERT INTO rol (nombre, descripcion) VALUES
('cliente', 'Usuario final de la plataforma'),
('dueno',   'Propietario de Negocio'),
('admin_plataforma', 'Administrador Global');
