CREATE TABLE [dbo].[tTabletDataInsertArchive] (
    [TabletDataInsertArchiveID] INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [CreatedUserUID]            VARCHAR (100)      NULL,
    [SvrDTLT]                   DATETIME           CONSTRAINT [DF_t_TabletDataInsertArchive_SvrDTLT] DEFAULT (getdate()) NULL,
    [SvrDTLTOffset]             DATETIMEOFFSET (7) CONSTRAINT [DF_t_TabletDataInsertArchive_SvrDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [TransactionType]           VARCHAR (100)      NULL,
    [InsertedData]              VARCHAR (MAX)      NULL,
    CONSTRAINT [PK_t_TabletDataInsertArchive] PRIMARY KEY CLUSTERED ([TabletDataInsertArchiveID] ASC)
);

