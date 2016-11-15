CREATE TABLE [dbo].[tWEBDataInsertArchive] (
    [WEBDataInsertArchiveID] INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [CreatedUserUID]         VARCHAR (100)      NOT NULL,
    [SvrDTLT]                DATETIME           CONSTRAINT [DF_t_tWEBDataInsertArchive_SvrDTLT] DEFAULT (getdate()) NULL,
    [SvrDTLTOffset]          DATETIMEOFFSET (7) CONSTRAINT [DF_t_tWEBDataInsertArchive_SvrDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [TransactionType]        VARCHAR (100)      NULL,
    [InsertedData]           VARCHAR (MAX)      NULL,
    CONSTRAINT [PK_tWEBDataInsertArchive] PRIMARY KEY CLUSTERED ([WEBDataInsertArchiveID] ASC)
);

